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
use JWeiland\Yellowpages2\Domain\Repository\CategoryRepository;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Domain\Repository\DistrictRepository;
use JWeiland\Yellowpages2\Domain\Repository\FeUserRepository;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * An abstract controller which keeps useful methods for all other controllers
 */
class AbstractController extends ActionController
{
    /**
     * @var MailMessage
     */
    protected $mail;

    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

    /**
     * @var DistrictRepository
     */
    protected $districtRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var FeUserRepository
     */
    protected $feUserRepository;

    /**
     * @var Session
     */
    protected $session;

    public function injectMail(MailMessage $mail): void
    {
        $this->mail = $mail;
    }

    public function injectExtConf(ExtConf $extConf): void
    {
        $this->extConf = $extConf;
    }

    public function injectPersistenceManager(PersistenceManager $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    public function injectCompanyRepository(CompanyRepository $companyRepository): void
    {
        $this->companyRepository = $companyRepository;
    }

    public function injectDistrictRepository(DistrictRepository $districtRepository): void
    {
        $this->districtRepository = $districtRepository;
    }

    public function injectCategoryRepository(CategoryRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function injectFeUserRepository(FeUserRepository $feUserRepository): void
    {
        $this->feUserRepository = $feUserRepository;
    }

    public function injectSession(Session $session): void
    {
        $this->session = $session;
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
     * This is a workaround to help controller actions to find (hidden) companies
     *
     * @param string $argumentName
     */
    protected function registerCompanyFromRequest(string $argumentName): void
    {
        $argument = $this->request->getArgument($argumentName);
        if (is_array($argument)) {
            // get company from form ($_POST)
            $company = $this->companyRepository->findHiddenEntryByUid((int)$argument['__identity']);
        } else {
            // get company from UID
            $company = $this->companyRepository->findHiddenEntryByUid((int)$argument);
        }
        $this->session->registerObject($company, $company->getUid());
    }

    /**
     * A template method for displaying custom error flash messages, or to
     * display no flash message at all on errors. Override this to customize
     * the flash message in your action controller.
     *
     * @return string
     */
    protected function getErrorFlashMessage(): string
    {
        return LocalizationUtility::translate(
            'errorFlashMessage',
            'yellowpages2',
            [
                get_class($this),
                $this->actionMethodName
            ]
        );
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
            $poiCollection = $this->objectManager->get(PoiCollection::class);
            $poiCollection->setCollectionType('Point');
            $poiCollection->setTitle($company->getCompany());
            $poiCollection->setLatitude($position->getLatitude());
            $poiCollection->setLongitude($position->getLongitude());
            $poiCollection->setAddress($position->getFormattedAddress());
            $company->setTxMaps2Uid($poiCollection);
        } else {
            DebuggerUtility::var_dump($position);
            throw new \Exception('Can\'t find a result for address: ' . $company->getAddress() . '. Activate Debugging for a more detailed output.', 1465474954);
        }
    }
}
