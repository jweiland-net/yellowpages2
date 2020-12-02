<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Controller;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Yellowpages2\Configuration\ExtConf;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Helper\HiddenObjectHelper;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to show and save PoiCollections on a map
 */
class MapController extends ActionController
{
    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    public function __construct(
        CompanyRepository $companyRepository,
        PersistenceManagerInterface $persistenceManager
    ) {
        $this->companyRepository = $companyRepository;
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @param Company $company
     */
    public function newAction(Company $company): void
    {
        $this->addNewPoiCollectionToCompany($company);
        $this->view->assign('company', $company);
    }

    /**
     * "create" means adding a new poi to company, but company itself has to be updated
     *
     * @param Company $company
     */
    public function createAction(Company $company): void
    {
        $this->sendMail('create', $company);
        $company->setHidden(true);
        $this->companyRepository->update($company);
        $this->addFlashMessage(LocalizationUtility::translate('companyCreated', 'yellowpages2'));
        $this->redirect('listMyCompanies', 'Company');
    }

    public function initializeEditAction(): void
    {
        $hiddenObjectHelper = $this->objectManager->get(HiddenObjectHelper::class);
        $hiddenObjectHelper->registerHiddenObjectInExtbaseSession(
            $this->companyRepository,
            $this->request,
            'company'
        );
    }

    /**
     * @param Company $company
     */
    public function editAction(Company $company): void
    {
        $this->view->assign('company', $company);
    }

    /**
     * Allow editing of SubModel
     */
    public function initializeUpdateAction(): void
    {
        $hiddenObjectHelper = $this->objectManager->get(HiddenObjectHelper::class);
        $hiddenObjectHelper->registerHiddenObjectInExtbaseSession(
            $this->companyRepository,
            $this->request,
            'company'
        );
    }

    /**
     * @param Company $company
     */
    public function updateAction(Company $company): void
    {
        // if an admin edits this hidden record mail should not be send
        if (!$company->getHidden()) {
            $this->sendMail('update', $company);
        }
        $company->setHidden(true);
        $this->companyRepository->update($company);
        $this->addFlashMessage(LocalizationUtility::translate('companyUpdated', 'yellowpages2'));
        $this->redirect('listMyCompanies', 'Company');
    }

    /**
     * Add new PoiCollection to Company, if company is new
     *
     * @param Company $company
     * @throws \Exception
     */
    protected function addNewPoiCollectionToCompany(Company $company): void
    {
        $geoCodeService = GeneralUtility::makeInstance(GeoCodeService::class);
        $position = $geoCodeService->getFirstFoundPositionByAddress($company->getAddress());
        if ($position instanceof Position) {
            $poiCollection = GeneralUtility::makeInstance(PoiCollection::class);
            $poiCollection->setCollectionType('Point');
            $poiCollection->setTitle($company->getCompany());
            $poiCollection->setLatitude($position->getLatitude());
            $poiCollection->setLongitude($position->getLongitude());
            $poiCollection->setAddress($position->getFormattedAddress());
            $company->setTxMaps2Uid($poiCollection);
            $this->companyRepository->update($company);
            $this->persistenceManager->persistAll();
        } else {
            $this->controllerContext->getFlashMessageQueue()->enqueue(...$geoCodeService->getErrors());
            $this->redirect('edit', 'Company', null, ['company' => $company]);
        }
    }

    /**
     * send email on new/update
     *
     * @param string $subjectKey
     * @param Company $company
     * @return bool
     */
    public function sendMail(string $subjectKey, Company $company): bool
    {
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $extConf = GeneralUtility::makeInstance(ExtConf::class);
        $this->view->assign('company', $company);
        $mailContent = $this->view->render();

        $mail->setFrom($extConf->getEmailFromAddress(), $extConf->getEmailFromName());
        $mail->setTo($extConf->getEmailToAddress(), $extConf->getEmailToName());
        $mail->setSubject(LocalizationUtility::translate('email.subject.' . $subjectKey, 'yellowpages2'));
        if (method_exists($mail, 'addPart')) {
            // TYPO3 < 10 (Swift_Message)
            $mail->setBody($mailContent, 'text/html');
        } else {
            // TYPO3 >= 10 (Symfony Mail)
            $mail->html($mailContent);
        }

        return $mail->send();
    }

    public function getTemplatePathForMail(): string
    {
        $extKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
        return ExtensionManagementUtility::extPath($extKey) . 'Resources/Private/Templates/Email/';
    }
}
