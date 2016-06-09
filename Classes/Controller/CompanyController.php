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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CompanyController extends AbstractController
{
    /**
     * action list
     *
     * @param string $letter Show only records starting with this letter
     * @validate $letter String, StringLength(minimum=0,maximum=3)
     * @return void
     */
    public function listAction($letter = null)
    {
        $companies = $this->companyRepository->findByStartingLetter($letter, $this->settings);

        $this->view->assign('companies', $companies);
        $this->view->assign('glossar', $this->getGlossar($this->settings['showWspMembers']));
        $this->view->assign('categories', $this->companyRepository->getGroupedCategories());
    }

    /**
     * action listMyCompanies
     *
     * @return void
     */
    public function listMyCompaniesAction()
    {
        $companies = $this->companyRepository->findByFeUser($GLOBALS['TSFE']->fe_user->user['uid']);
        $this->view->assign('companies', $companies);
        $this->view->assign('categories', $this->companyRepository->getGroupedCategories());
    }

    /**
     * action show
     *
     * @param integer $company
     * @return void
     */
    public function showAction($company)
    {
        $companyObject = $this->companyRepository->findByIdentifier($company);
        $this->view->assign('company', $companyObject);
    }

    /**
     * secure search parameter
     *
     * @return void
     */
    public function initializeSearchAction()
    {
        if ($this->request->hasArgument('search')) {
            $search = $this->request->getArgument('search');
            $this->request->setArgument('search', htmlspecialchars($search));
        }
    }

    /**
     * search show
     *
     * @param string $search
     * @param integer $category
     * @return void
     */
    public function searchAction($search, $category = 0)
    {
        $companies = $this->companyRepository->searchCompanies($search, $category);
        $this->view->assign('search', $search);
        $this->view->assign('category', $category);
        $this->view->assign('companies', $companies);
        $this->view->assign('glossar', $this->getGlossar($this->settings['showWspMembers']));
        $this->view->assign('categories', $this->companyRepository->getGroupedCategories());
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        /** @var \JWeiland\Yellowpages2\Domain\Model\Company $company */
        $company = $this->objectManager->get('JWeiland\\Yellowpages2\\Domain\\Model\\Company');
        $district = $this->districtRepository->findByUid($this->settings['uidOfDefaultDistrict']);
        if ($district instanceof \JWeiland\Yellowpages2\Domain\Model\District) {
            $company->setDistrict($district);
        }

        // get available categories and add "Please choose" to first position
        $categories = $this->categoryRepository->findByParent($this->settings['startingUidForCategories']);

        $this->view->assign('company', $company);
        $this->view->assign('districts', $this->districtRepository->getDistricts());
        $this->view->assign('categories', $categories);
    }

    /**
     * initialize create action
     * allow creation of submodel category
     *
     * @return void
     */
    public function initializeCreateAction()
    {
        /** @var \JWeiland\Yellowpages2\Property\TypeConverter\UploadOneFileConverter $oneFileTypeConverter */
        $oneFileTypeConverter = $this->objectManager->get('JWeiland\\Yellowpages2\\Property\\TypeConverter\\UploadOneFileConverter');
        $this->arguments->getArgument('company')->getPropertyMappingConfiguration()->forProperty('logo')->setTypeConverter($oneFileTypeConverter)->setTypeConverterOption('JWeiland\\Yellowpages2\\Property\\TypeConverter\\UploadOneFileConverter', 'TABLENAME', 'tx_yellowpages2_domain_model_company');
        /** @var \JWeiland\Yellowpages2\Property\TypeConverter\UploadMultipleFilesConverter $multipleFilesTypeConverter */
        $multipleFilesTypeConverter = $this->objectManager->get('JWeiland\\Yellowpages2\\Property\\TypeConverter\\UploadMultipleFilesConverter');
        $this->arguments->getArgument('company')->getPropertyMappingConfiguration()->forProperty('images')->setTypeConverter($multipleFilesTypeConverter)->setTypeConverterOption('JWeiland\\Yellowpages2\\Property\\TypeConverter\\UploadMultipleFilesConverter', 'TABLENAME', 'tx_yellowpages2_domain_model_company');
        $this->removeEmptyArgumentsFromRequest();
    }

    /**
     * action create
     *
     * @param \JWeiland\Yellowpages2\Domain\Model\Company $company
     * @return void
     */
    public function createAction(\JWeiland\Yellowpages2\Domain\Model\Company $company)
    {
        $this->deleteUploadedFilesOnValidationErrors('company');

        // set current feUser
        $feUser = $this->feUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
        $company->setFeUser($feUser);

        // set map record
        $results = $this->geocodeUtility->findPositionByAddress($company->getAddress());
        if (count($results)) {
            $results->rewind();
            /** @var \JWeiland\Maps2\Domain\Model\RadiusResult $result */
            $result = $results->current();
            /** @var \JWeiland\Maps2\Domain\Model\PoiCollection $poi */
            $poi = $this->objectManager->get('JWeiland\\Maps2\\Domain\\Model\\PoiCollection');
            $poi->setCollectionType('Point');
            $poi->setTitle($company->getCompany());
            $poi->setAddress($result->getFormattedAddress());
            $poi->setLatitude($result->getGeometry()->getLocation()->getLatitude());
            $poi->setLongitude($result->getGeometry()->getLocation()->getLongitude());
            $poi->setLatitudeOrig($result->getGeometry()->getLocation()->getLatitude());
            $poi->setLongitudeOrig($result->getGeometry()->getLocation()->getLongitude());
            $company->setTxMaps2Uid($poi);

            // save company
            // for redirecting we need to persist current object
            $this->companyRepository->add($company);
            $this->persistenceManager->persistAll();
            $this->addFlashMessage(LocalizationUtility::translate('companyCreated', 'yellowpages2'));
            $this->redirect('new', 'Map', 'yellowpages2', array('company' => $company));
        } else {
            $this->flashMessageContainer->add('Error while creating a poi. Please add some more informations about your address');
            $this->forward('new', 'Company', 'yellowpages2', array('company' => $company));
        }
    }

    /**
     * initialize edit action
     * This only happens if webko clicks on edit link in mail
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
     * @param integer $company
     * @return void
     */
    public function editAction($company)
    {
        $companyObject = $this->companyRepository->findByIdentifier($company);
        // get available categories and add "Please choose" to first position
        $categories = $this->categoryRepository->findByParent($this->settings['startingUidForCategories']);

        $this->view->assign('company', $companyObject);
        $this->view->assign('districts', $this->districtRepository->getDistricts());
        $this->view->assign('categories', $categories);
    }

    /**
     * initialize update action
     * allow editing of submodel category
     *
     * @return void
     */
    public function initializeUpdateAction()
    {
        $argument = $this->request->getArgument('company');
        /** @var \JWeiland\Yellowpages2\Domain\Model\Company $company */
        $company = $this->companyRepository->findByIdentifier($argument['__identity']);
        /** @var \JWeiland\Yellowpages2\Property\TypeConverter\UploadOneFileConverter $oneFileTypeConverter */
        $oneFileTypeConverter = $this->objectManager->get('JWeiland\\Yellowpages2\\Property\\TypeConverter\\UploadOneFileConverter');
        $this->arguments->getArgument('company')
            ->getPropertyMappingConfiguration()
            ->forProperty('logo')
            ->setTypeConverter($oneFileTypeConverter)
            ->setTypeConverterOptions('JWeiland\\Yellowpages2\\Property\\TypeConverter\\UploadOneFileConverter', array(
                'TABLENAME' => 'tx_yellowpages2_domain_model_company',
                'IMAGE' => $company->getLogo()
            ));
        /** @var \JWeiland\Yellowpages2\Property\TypeConverter\UploadMultipleFilesConverter $multipleFilesTypeConverter */
        $multipleFilesTypeConverter = $this->objectManager->get('JWeiland\\Yellowpages2\\Property\\TypeConverter\\UploadMultipleFilesConverter');
        $this->arguments->getArgument('company')
            ->getPropertyMappingConfiguration()
            ->forProperty('images')
            ->setTypeConverter($multipleFilesTypeConverter)
            ->setTypeConverterOptions('JWeiland\\Yellowpages2\\Property\\TypeConverter\\UploadMultipleFilesConverter', array(
                'TABLENAME' => 'tx_yellowpages2_domain_model_company',
                'IMAGES' => $company->getImages()
            ));
    }

    /**
     * action update
     *
     * @param \JWeiland\Yellowpages2\Domain\Model\Company $company
     * @return void
     */
    public function updateAction(\JWeiland\Yellowpages2\Domain\Model\Company $company)
    {
        $this->companyRepository->update($company);

        // for redirecting we need to persist current object
        $this->persistenceManager->persistAll();

        $this->addFlashMessage(LocalizationUtility::translate('companyUpdated', 'yellowpages2'));
        $this->redirect('edit', 'Map', 'yellowpages2', array('company' => $company));
    }

    /**
     * initialize activate action
     *
     * @return void
     */
    public function initializeActivateAction()
    {
        $this->registerCompanyFromRequest('company');
    }

    /**
     * action activate
     *
     * @param integer $company
     * @return void
     */
    public function activateAction($company)
    {
        $companyObject = $this->companyRepository->findByIdentifier($company);
        $companyObject->setHidden(false);
        $this->companyRepository->update($companyObject);

        // send mail
        $this->view->assign('company', $companyObject);

        $this->mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
        $this->mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
        if ($companyObject->getEmail() && $companyObject->getCompany()) {
            $this->mail->addCc($companyObject->getEmail(), $companyObject->getCompany());
        }
        $this->mail->setSubject(LocalizationUtility::translate('email.subject.activate', 'yellowpages2'));
        $this->mail->setBody($this->view->render(), 'text/html');
        $this->mail->send();

        $this->redirect('list', 'Company');
    }
}
