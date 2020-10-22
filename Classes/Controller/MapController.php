<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Controller;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Yellowpages2\Domain\Model\Company;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to show and save PoiCollections on a map
 */
class MapController extends AbstractController
{
    /**
     * @var ExtConf
     */
    protected $extConfOfMaps2;

    public function injectExtConfOfMaps2(ExtConf $extConfOfMaps2): void
    {
        $this->extConfOfMaps2 = $extConfOfMaps2;
    }

    /**
     * Hint: no "validate" Annotation: company was saved in previously called action
     *
     * @param Company|null $company
     */
    public function newAction(?Company $company): void
    {
        if ($company === null) {
            $company = $this->objectManager->get(Company::class);
        }

        $this->addNewPoiCollectionToCompany($company);
        $this->companyRepository->update($company);
        $this->persistenceManager->persistAll();
        $this->view->assign('company', $company);
    }

    /**
     * Allow modification of submodel
     * Hint: submodel was created already in companyController, that's why we need modification here
     */
    public function initializeCreateAction(): void
    {
        $maps2Request = GeneralUtility::_POST('tx_maps2');
        if ($maps2Request !== null) {
            /** @var array $company */
            $company = $this->request->getArgument('company');
            $company['txMaps2Uid'] = $maps2Request;
            $this->request->setArgument('company', $company);
        }
        $this->arguments->getArgument('company')->getPropertyMappingConfiguration()->allowModificationForSubProperty('txMaps2Uid');
        $this->arguments->getArgument('company')->getPropertyMappingConfiguration()
            ->allowProperties('txMaps2Uid')
            ->forProperty('txMaps2Uid')->allowProperties('latitude', 'longitude', '__identity');
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
        $this->redirect('listMyCompanies', 'Company');
    }

    public function initializeEditAction(): void
    {
        $this->registerCompanyFromRequest('company');
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
        $maps2Request = GeneralUtility::_POST('tx_maps2');
        if ($maps2Request !== null) {
            /** @var array $company */
            $company = $this->request->getArgument('company');
            $company['txMaps2Uid'] = $maps2Request;
            $this->request->setArgument('company', $company);
        }
        $this->registerCompanyFromRequest('company');
        $this->arguments->getArgument('company')->getPropertyMappingConfiguration()->allowCreationForSubProperty('txMaps2Uid');
        $this->arguments->getArgument('company')->getPropertyMappingConfiguration()->allowModificationForSubProperty('txMaps2Uid');
        $this->arguments->getArgument('company')->getPropertyMappingConfiguration()->allowProperties('txMaps2Uid');
        $this->arguments->getArgument('company')->getPropertyMappingConfiguration()->forProperty('txMaps2Uid')->allowProperties('latitude', 'longitude');
    }

    /**
     * @param Company $company
     */
    public function updateAction(Company $company): void
    {
        // if webko edits this hidden record, mail should not be send
        if (!$company->getHidden()) {
            $this->sendMail('update', $company);
        }
        $company->setHidden(true);
        $this->companyRepository->update($company);
        $this->redirect('listMyCompanies', 'Company');
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
        $this->view->assign('company', $company);

        $this->mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
        $this->mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
        $this->mail->setSubject(LocalizationUtility::translate('email.subject.' . $subjectKey, 'yellowpages2'));
        if (method_exists($this->mail, 'addPart')) {
            // TYPO3 < 10 (Swift_Message)
            $this->mail->setBody($this->view->render(), 'text/html');
        } else {
            $isSymfonyEmail = true;
            // TYPO3 >= 10 (Symfony Mail)
            $this->mail->html($this->view->render());
        }

        return $this->mail->send();
    }

    public function getTemplatePathForMail(): string
    {
        $extKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
        return ExtensionManagementUtility::extPath($extKey) . 'Resources/Private/Templates/Email/';
    }
}
