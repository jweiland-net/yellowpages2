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
use JWeiland\Yellowpages2\Configuration\ExtConf;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Model\District;
use JWeiland\Yellowpages2\Domain\Repository\CategoryRepository;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Domain\Repository\DistrictRepository;
use JWeiland\Yellowpages2\Domain\Repository\FeUserRepository;
use JWeiland\Yellowpages2\Helper\HiddenObjectHelper;
use JWeiland\Yellowpages2\Helper\PathSegmentHelper;
use JWeiland\Yellowpages2\Property\TypeConverter\UploadMultipleFilesConverter;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Property\TypeConverterInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to list, show and search for companies
 */
class CompanyController extends ActionController
{
    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var DistrictRepository
     */
    protected $districtRepository;

    /**
     * @var FeUserRepository
     */
    protected $feUserRepository;

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var GlossaryService
     */
    protected $glossaryService;

    public function __construct(
        CompanyRepository $companyRepository,
        CategoryRepository $categoryRepository,
        DistrictRepository $districtRepository,
        FeUserRepository $feUserRepository,
        PersistenceManagerInterface $persistenceManager,
        GlossaryService $glossaryService
    ) {
        $this->companyRepository = $companyRepository;
        $this->categoryRepository = $categoryRepository;
        $this->districtRepository = $districtRepository;
        $this->feUserRepository = $feUserRepository;
        $this->persistenceManager = $persistenceManager;
        $this->glossaryService = $glossaryService;
    }

    public function initializeAction(): void
    {
        // if this value was not set, then it will be filled with 0
        // but that is not good, because UriBuilder accepts 0 as pid, so it's better to set it to NULL
        if (empty($this->settings['pidOfDetailPage'])) {
            $this->settings['pidOfDetailPage'] = null;
        }
        if (empty($this->settings['pidOfListPage'])) {
            $this->settings['pidOfListPage'] = null;
        }
    }

    /**
     * @param string $letter
     * @TYPO3\CMS\Extbase\Annotation\Validate("String", param="letter")
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
        $company = GeneralUtility::makeInstance(Company::class);
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

    public function initializeCreateAction(): void
    {
        $this->removeEmptyTrades();
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

        /** @var FrontendUser $feUser */
        $feUser = $this->feUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
        $company->setFeUser($feUser);
        $this->companyRepository->add($company);

        $pathSegmentHelper = GeneralUtility::makeInstance(PathSegmentHelper::class);
        $pathSegmentHelper->updatePathSegmentForCompany($company);

        $this->companyRepository->update($company);
        $this->persistenceManager->persistAll();

        if (ExtensionManagementUtility::isLoaded('maps2')) {
            $this->redirect('new', 'Map', 'yellowpages2', ['company' => $company]);
        }
        $this->addFlashMessage(LocalizationUtility::translate('companyCreated', 'yellowpages2'));
        $this->redirect('listMyCompanies', 'Company');
    }

    /**
     * Will be called when link in mail will be clicked
     */
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
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("company")
     */
    public function editAction(Company $company): void
    {
        $this->view->assignMultiple([
            'company' => $company,
            'districts' => $this->districtRepository->getDistricts(),
            'categories' => $this->categoryRepository->findByParent((int)$this->settings['startingUidForCategories'])
        ]);
    }

    public function initializeUpdateAction(): void
    {
        $this->removeEmptyTrades();
        $hiddenObjectHelper = $this->objectManager->get(HiddenObjectHelper::class);
        $hiddenObjectHelper->registerHiddenObjectInExtbaseSession(
            $this->companyRepository,
            $this->request,
            'company'
        );
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

        if (ExtensionManagementUtility::isLoaded('maps2')) {
            if ($company->getTxMaps2Uid() === null) {
                $this->redirect('new', 'Map', null, ['company' => $company]);
            } else {
                $this->redirect('edit', 'Map', null, ['company' => $company]);
            }
        } else {
            $this->addFlashMessage(LocalizationUtility::translate('companyUpdated', 'yellowpages2'));
            $this->redirect('listMyCompanies', 'Company');
        }
    }

    public function initializeActivateAction(): void
    {
        $hiddenObjectHelper = $this->objectManager->get(HiddenObjectHelper::class);
        $hiddenObjectHelper->registerHiddenObjectInExtbaseSession(
            $this->companyRepository,
            $this->request,
            'company'
        );
    }

    /**
     * @param int $company
     */
    public function activateAction(int $company): void
    {
        $companyObject = $this->companyRepository->findByIdentifier($company);
        $companyObject->setHidden(false);
        $this->companyRepository->update($companyObject);

        $extConf = GeneralUtility::makeInstance(ExtConf::class);
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $this->view->assign('company', $companyObject);
        $mailContent = $this->view->render();

        $mail->setFrom($extConf->getEmailFromAddress(), $extConf->getEmailFromName());
        $mail->setTo($extConf->getEmailToAddress(), $extConf->getEmailToName());
        if ($companyObject->getEmail() && $companyObject->getCompany()) {
            $mail->addCc($companyObject->getEmail(), $companyObject->getCompany());
        }
        $mail->setSubject(LocalizationUtility::translate('email.subject.activate', 'yellowpages2'));
        if (method_exists($mail, 'addPart')) {
            // TYPO3 < 10 (Swift_Message)
            $mail->setBody($mailContent, 'text/html');
        } else {
            // TYPO3 >= 10 (Symfony Mail)
            $mail->html($mailContent);
        }
        $mail->send();

        $this->redirect('list', 'Company');
    }

    /**
     * Currently only "logo" and "images" are allowed properties.
     *
     * @param string $property
     * @param MvcPropertyMappingConfiguration $propertyMappingConfigurationForCompany
     * @param mixed $converterOptionValue
     */
    protected function assignMediaTypeConverter(
        string $property,
        MvcPropertyMappingConfiguration $propertyMappingConfigurationForCompany,
        $converterOptionValue
    ): void {
        if ($property === 'logo' || $property === 'images') {
            $className = UploadMultipleFilesConverter::class;
            $converterOptionName = 'IMAGES';
        } else {
            return;
        }

        /** @var TypeConverterInterface $typeConverter */
        $typeConverter = $this->objectManager->get($className);
        $propertyMappingConfigurationForMediaFiles = $propertyMappingConfigurationForCompany
            ->forProperty($property)
            ->setTypeConverter($typeConverter);

        $propertyMappingConfigurationForMediaFiles->setTypeConverterOption(
            $className,
            'settings',
            $this->settings
        );

        if (!empty($converterOptionValue)) {
            // Do not use setTypeConverterOptions() as this will remove all existing options
            $propertyMappingConfigurationForMediaFiles->setTypeConverterOption(
                $className,
                $converterOptionName,
                $converterOptionValue
            );
        }
    }

    /**
     * Remove empty arguments from request
     */
    protected function removeEmptyArgumentsFromRequest(): void
    {
        $company = $this->request->getArgument('company');
        $company['trades'] = ArrayUtility::removeArrayEntryByValue($company['trades'], '');
        if ($company['trades'] === []) {
            unset($company['trades']);
        }
        $this->request->setArgument('company', $company);
    }

    /**
     * files will be uploaded in typeConverter automatically
     * But, if an error occurs we have to remove them
     *
     * @param string $argument
     */
    protected function deleteUploadedFilesOnValidationErrors(string $argument): void
    {
        if ($this->getControllerContext()->getRequest()->hasArgument($argument)) {
            /** @var array $company */
            $company = $this->getControllerContext()->getRequest()->getArgument($argument);
            if ($company['images'] !== []) {
                unset($company['images']);
            }
            if ($company['logo'] !== []) {
                unset($company['logo']);
            }
            $this->getControllerContext()->getRequest()->setArgument($argument, $company);
        }
    }

    protected function removeEmptyTrades(): void
    {
        if ($this->request->hasArgument('company')) {
            $company = $this->request->getArgument('company');
            $company['trades'] = array_filter($company['trades']);
            $this->request->setArgument('company', $company);
        }
    }

    protected function assignGlossary(): void
    {
        $this->view->assign(
            'glossar',
            $this->glossaryService->buildGlossary(
                $this->companyRepository->getQueryBuilderToFindAllEntries(),
                [
                    'extensionName' => 'yellowpages2',
                    'pluginName' => 'directory',
                    'controllerName' => 'Company',
                    'column' => 'company'
                ]
            )
        );
    }
}
