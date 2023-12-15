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
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Helper\MailHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to show and save PoiCollections on a map
 */
class MapController extends AbstractController
{
    protected CompanyRepository $companyRepository;

    protected PersistenceManagerInterface $persistenceManager;

    protected MailHelper $mailHelper;

    public function injectCompanyRepository(CompanyRepository $companyRepository): void
    {
        $this->companyRepository = $companyRepository;
    }

    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    public function injectMailHelper(MailHelper $mailHelper): void
    {
        $this->mailHelper = $mailHelper;
    }

    public function newAction(Company $company): void
    {
        $this->addNewPoiCollectionToCompany($company);

        $this->postProcessAndAssignFluidVariables([
            'company' => $company,
        ]);
    }

    /**
     * "create" means adding a new poi to company, but company itself has to be updated
     */
    public function createAction(Company $company): void
    {
        $company->setHidden(true);
        $this->companyRepository->update($company);
        $this->postProcessControllerAction($company);

        $this->sendMail('create', $company);

        $this->addFlashMessage(LocalizationUtility::translate('companyCreated', 'yellowpages2'));
        $this->redirect('listMyCompanies', 'Company');
    }

    public function initializeEditAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function editAction(Company $company): void
    {
        $this->postProcessAndAssignFluidVariables([
            'company' => $company,
        ]);
    }

    /**
     * Allow editing of SubModel
     */
    public function initializeUpdateAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function updateAction(Company $company): void
    {
        // If an admin edits this hidden record, mail should not be sent.
        if (!$company->getHidden()) {
            $this->sendMail('update', $company);
        }

        $company->setHidden(true);
        $this->companyRepository->update($company);

        $this->postProcessControllerAction($company);

        $this->addFlashMessage(LocalizationUtility::translate('companyUpdated', 'yellowpages2'));
        $this->redirect('listMyCompanies', 'Company');
    }

    /**
     * Add new PoiCollection to Company, if company is new
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

    public function sendMail(string $subjectKey, Company $company): void
    {
        $this->postProcessAndAssignFluidVariables([
            'company' => $company,
        ]);

        $this->mailHelper->sendMail(
            $this->view->render(),
            LocalizationUtility::translate('email.subject.' . $subjectKey, 'yellowpages2')
        );
    }
}
