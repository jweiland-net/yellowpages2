<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Controller;

use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Model\District;
use JWeiland\Yellowpages2\Domain\Model\FeUser;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to list, show and search for companies
 */
class CompanyController extends AbstractController
{
    /**
     * @var GlossaryService
     */
    protected $glossaryService;

    public function injectGlossaryService(GlossaryService $glossaryService): void
    {
        $this->glossaryService = $glossaryService;
    }

    /**
     * @param string $letter
     * @TYPO3\CMS\Extbase\Annotation\Validate("StringLength", param="letter", options={"minimum": 0, "maximum": 3})
     */
    public function listAction(string $letter = ''): void
    {
        $this->view->assignMultiple([
            'companies' => $this->companyRepository->findByLetter($letter, $this->settings),
            'categories' => $this->companyRepository->getTranslatedCategories()
        ]);
        $this->assignGlossary();
    }

    public function listMyCompaniesAction(): void
    {
        $this->view->assignMultiple([
            'companies' => $this->companyRepository->findByFeUser((int)$GLOBALS['TSFE']->fe_user->user['uid']),
            'categories' => $this->companyRepository->getTranslatedCategories()
        ]);
    }

    /**
     * @param int $company
     */
    public function showAction(int $company): void
    {
        $companyObject = $this->companyRepository->findByIdentifier($company);
        $this->view->assign('company', $companyObject);
    }

    /**
     * Secure search parameter
     */
    public function initializeSearchAction(): void
    {
        if ($this->request->hasArgument('search')) {
            $search = $this->request->getArgument('search');
            $this->request->setArgument('search', htmlspecialchars($search));
        }
    }

    /**
     * @param string $search
     * @param int $category
     */
    public function searchAction(string $search, int $category = 0): void
    {
        $this->view->assignMultiple([
            'search' => $search,
            'category' => $category,
            'companies' => $this->companyRepository->searchCompanies($search, $category, $this->settings),
            'categories' => $this->companyRepository->getTranslatedCategories()
        ]);
        $this->assignGlossary();
    }

    public function newAction(): void
    {
        /** @var Company $company */
        $company = $this->objectManager->get(Company::class);
        $district = $this->districtRepository->findByUid($this->settings['uidOfDefaultDistrict']);
        if ($district instanceof District) {
            $company->setDistrict($district);
        }

        $this->view->assignMultiple([
            'company' => $company,
            'districts' => $this->districtRepository->getDistricts(),
            'categories' => $this->categoryRepository->findByParent($this->settings['startingUidForCategories'])
        ]);
    }

    /**
     * Allow creation of submodel category
     */
    public function initializeCreateAction(): void
    {
        $companyMappingConfiguration = $this->arguments
            ->getArgument('company')
            ->getPropertyMappingConfiguration();

        $this->assignMediaTypeConverter('logo', $companyMappingConfiguration, null);
        $this->assignMediaTypeConverter('images', $companyMappingConfiguration, null);

        $this->removeEmptyArgumentsFromRequest();
    }

    /**
     * @param Company $company
     */
    public function createAction(Company $company): void
    {
        $this->deleteUploadedFilesOnValidationErrors('company');

        // set current feUser
        /** @var FeUser $feUser */
        $feUser = $this->feUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
        $company->setFeUser($feUser);

        if (ExtensionManagementUtility::isLoaded('maps2')) {
            $geoCodeService = GeneralUtility::makeInstance(GeoCodeService::class);
            $position = $geoCodeService->getFirstFoundPositionByAddress($company->getAddress());
            if ($position instanceof Position) {
                $poi = $this->objectManager->get(PoiCollection::class);
                $poi->setCollectionType('Point');
                $poi->setTitle($company->getCompany());
                $poi->setAddress($position->getFormattedAddress());
                $poi->setLatitude($position->getLatitude());
                $poi->setLongitude($position->getLongitude());
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
        $this->redirect('listMyCompanies', 'Company');
    }

    /**
     * Will be called when link in mail will be clicked
     */
    public function initializeEditAction(): void
    {
        $this->registerCompanyFromRequest('company');
    }

    /**
     * @param Company $company
     */
    public function editAction(Company $company): void
    {
        $this->view->assignMultiple([
            'company' => $company,
            'districts' => $this->districtRepository->getDistricts(),
            'categories' => $this->categoryRepository->findByParent((int)$this->settings['startingUidForCategories'])
        ]);
    }

    /**
     * Allow editing of submodel category
     */
    public function initializeUpdateAction(): void
    {
        $this->registerCompanyFromRequest('company');
        $requestArgument = $this->request->getArgument('company');

        $companyMappingConfiguration = $this->arguments
            ->getArgument('company')
            ->getPropertyMappingConfiguration();

        // Needed to get the previously stored logo and images
        /** @var Company $persistedCompany */
        $persistedCompany = $this->companyRepository->findByIdentifier($requestArgument['__identity']);
        $this->assignMediaTypeConverter('logo', $companyMappingConfiguration, $persistedCompany->getOriginalLogo());
        $this->assignMediaTypeConverter('images', $companyMappingConfiguration, $persistedCompany->getOriginalImages());
    }

    /**
     * @param Company $company
     */
    public function updateAction(Company $company): void
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

    public function initializeActivateAction(): void
    {
        $this->registerCompanyFromRequest('company');
    }

    /**
     * @param int $company
     */
    public function activateAction(int $company): void
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
        if (method_exists($this->mail, 'addPart')) {
            // TYPO3 < 10 (Swift_Message)
            $this->mail->setBody($this->view->render(), 'text/html');
        } else {
            // TYPO3 >= 10 (Symfony Mail)
            $this->mail->html($this->view->render());
        }
        $this->mail->send();

        $this->redirect('list', 'Company');
    }

    protected function assignGlossary(): void
    {
        $this->view->assign('glossar', $this->glossaryService->buildGlossary(
            $this->companyRepository->getQueryBuilderToFindAllEntries(),
            [
                'extensionName' => 'yellowpages2',
                'pluginName' => 'directory',
                'controllerName' => 'Company',
                'column' => 'company'
            ]
        ));
    }
}
