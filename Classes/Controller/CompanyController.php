<?php
declare(strict_types=1);
namespace JWeiland\Yellowpages2\Controller;

/*
 * This file is part of the yellowpages2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\RadiusResult;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Model\District;
use JWeiland\Yellowpages2\Domain\Model\FeUser;
use JWeiland\Yellowpages2\Property\TypeConverter\UploadMultipleFilesConverter;
use JWeiland\Yellowpages2\Property\TypeConverter\UploadOneFileConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
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
     * @param int $company
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
     * @param int $category
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
        /** @var Company $company */
        $company = $this->objectManager->get(Company::class);
        $district = $this->districtRepository->findByUid($this->settings['uidOfDefaultDistrict']);
        if ($district instanceof District) {
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
        /** @var UploadOneFileConverter $oneFileTypeConverter */
        $oneFileTypeConverter = $this->objectManager->get(UploadOneFileConverter::class);
        $this->arguments->getArgument('company')->getPropertyMappingConfiguration()->forProperty('logo')->setTypeConverter($oneFileTypeConverter);
        /** @var UploadMultipleFilesConverter $multipleFilesTypeConverter */
        $multipleFilesTypeConverter = $this->objectManager->get(UploadMultipleFilesConverter::class);
        $this->arguments->getArgument('company')->getPropertyMappingConfiguration()->forProperty('images')->setTypeConverter($multipleFilesTypeConverter);
        $this->removeEmptyArgumentsFromRequest();
    }

    /**
     * action create
     *
     * @param Company $company
     * @return void
     */
    public function createAction(Company $company)
    {
        $this->deleteUploadedFilesOnValidationErrors('company');

        // set current feUser
        /** @var FeUser $feUser */
        $feUser = $this->feUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
        $company->setFeUser($feUser);

        // set map record
        $radiusResult = $this->googleMapsService->getFirstFoundPositionByAddress($company->getAddress());
        if ($radiusResult instanceof RadiusResult) {
            /** @var PoiCollection $poi */
            $poi = $this->objectManager->get(PoiCollection::class);
            $poi->setCollectionType('Point');
            $poi->setTitle($company->getCompany());
            $poi->setAddress($radiusResult->getFormattedAddress());
            $poi->setLatitude($radiusResult->getGeometry()->getLocation()->getLatitude());
            $poi->setLongitude($radiusResult->getGeometry()->getLocation()->getLongitude());
            $company->setTxMaps2Uid($poi);

            // save company
            // for redirecting we need to persist current object
            $this->companyRepository->add($company);
            $this->persistenceManager->persistAll();
            $this->addFlashMessage(LocalizationUtility::translate('companyCreated', 'yellowpages2'));
            $this->redirect('new', 'Map', 'yellowpages2', ['company' => $company]);
        } else {
            $this->addFlashMessage('Error while creating a poi. Please add some more informations about your address');
            $this->forward('new', 'Company', 'yellowpages2', ['company' => $company]);
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
     * @param Company $company
     * @return void
     */
    public function editAction(Company $company)
    {
        $companyObject = $company;
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
        $this->registerCompanyFromRequest('company');

        /** @var UploadOneFileConverter $oneFileTypeConverter */
        $oneFileTypeConverter = $this->objectManager->get(UploadOneFileConverter::class);
        $this->arguments->getArgument('company')
            ->getPropertyMappingConfiguration()
            ->forProperty('logo')
            ->setTypeConverter($oneFileTypeConverter);
        /** @var UploadMultipleFilesConverter $multipleFilesTypeConverter */
        $multipleFilesTypeConverter = $this->objectManager->get(UploadMultipleFilesConverter::class);
        $this->arguments->getArgument('company')
            ->getPropertyMappingConfiguration()
            ->forProperty('images')
            ->setTypeConverter($multipleFilesTypeConverter);
    }

    /**
     * action update
     *
     * @param Company $company
     * @return void
     */
    public function updateAction(Company $company)
    {
        $this->companyRepository->update($company);

        // for redirecting we need to persist current object
        $this->persistenceManager->persistAll();

        $this->addFlashMessage(LocalizationUtility::translate('companyUpdated', 'yellowpages2'));
        if ($company->getTxMaps2Uid() === null) {
            $this->redirect('new', 'Map', 'yellowpages2', ['company' => $company]);
        } else {
            $this->redirect('edit', 'Map', 'yellowpages2', ['company' => $company]);
        }
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
     * @param int $company
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
