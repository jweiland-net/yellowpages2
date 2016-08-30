<?php
namespace JWeiland\Yellowpages2\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use JWeiland\Yellowpages2\Domain\Model\Company;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MapController extends AbstractController
{
    /**
     * @var \JWeiland\Maps2\Configuration\ExtConf
     */
    protected $extConfOfMaps2;

    /**
     * inject extConfOfMaps2
     *
     * @param \JWeiland\Maps2\Configuration\ExtConf $extConfOfMaps2
     * @return void
     */
    public function injectExtConfOfMaps2(\JWeiland\Maps2\Configuration\ExtConf $extConfOfMaps2)
    {
        $this->extConfOfMaps2 = $extConfOfMaps2;
    }

    /**
     * action new
     * Hint: no "validate" Annotation: company was saved in previously called action
     *
     * @param Company $company
     * @return void
     */
    public function newAction(Company $company = null)
    {
        $this->addNewPoiCollectionToCompany($company);
        $this->companyRepository->update($company);
        $this->persistenceManager->persistAll();
        $this->view->assign('company', $company);
    }

    /**
     * initialize create action
     * allow modification of submodel
     * Hint: submodel was created already in companyController, that's why we need modification here
     *
     * @return void
     */
    public function initializeCreateAction()
    {
        $maps2Request = GeneralUtility::_POST('tx_maps2');
        if (isset($maps2Request)) {
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
     * action create
     * "create" means adding a new poi to company, but company itself has to be updated
     *
     * @param Company $company
     * @return void
     */
    public function createAction(Company $company)
    {
        $this->sendMail('create', $company);
        $company->setHidden(true);
        $this->companyRepository->update($company);
        $this->redirect('listMyCompanies', 'Company');
    }

    /**
     * initialize edit action
     *
     * @return void
     */
    public function initializeEditAction()
    {
        $this->registerCompanyFromRequest('company');
    }

    /**
     * action edit
     *
     * @param Company $company
     * @return void
     */
    public function editAction(Company $company)
    {
        $this->view->assign('company', $company);
    }

    /**
     * initialize update action
     * allow editing of SubModel
     *
     * @return void
     */
    public function initializeUpdateAction()
    {
        $maps2Request = GeneralUtility::_POST('tx_maps2');
        if (isset($maps2Request)) {
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
     * action update
     *
     * @param Company $company
     * @return void
     */
    public function updateAction(Company $company)
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
     * @return int The amount of email receivers
     */
    public function sendMail($subjectKey, Company $company)
    {
        $this->view->assign('company', $company);

        $this->mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
        $this->mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
        $this->mail->setSubject(LocalizationUtility::translate('email.subject.' . $subjectKey, 'yellowpages2'));
        $this->mail->setBody($this->view->render(), 'text/html');

        return $this->mail->send();
    }

    /**
     * get template path for email templates
     *
     * @return string email template path
     */
    public function getTemplatePath()
    {
        $extKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
        return ExtensionManagementUtility::extPath($extKey) . 'Resources/Private/Templates/Email/';
    }
}
