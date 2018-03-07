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
use JWeiland\Maps2\Utility\GeocodeUtility;
use JWeiland\Yellowpages2\Configuration\ExtConf;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CategoryRepository;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Domain\Repository\DistrictRepository;
use JWeiland\Yellowpages2\Domain\Repository\FeUserRepository;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
     * persistenceManager
     *
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * companyRepository
     *
     * @var CompanyRepository
     */
    protected $companyRepository;

    /**
     * districtRepository
     *
     * @var DistrictRepository
     */
    protected $districtRepository;

    /**
     * categoryRepository
     *
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * feUserRepository
     *
     * @var FeUserRepository
     */
    protected $feUserRepository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var GeocodeUtility
     */
    protected $geocodeUtility;

    /**
     * @var string
     */
    protected $letters = '0-9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';

    /**
     * inject mail
     *
     * @param MailMessage $mail
     * @return void
     */
    public function injectMail(MailMessage $mail)
    {
        $this->mail = $mail;
    }

    /**
     * inject extConf
     *
     * @param ExtConf $extConf
     * @return void
     */
    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * inject persistenceManager
     *
     * @param PersistenceManager $persistenceManager
     * @return void
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * inject companyRepository
     *
     * @param CompanyRepository $companyRepository
     * @return void
     */
    public function injectCompanyRepository(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    /**
     * inject districtRepository
     *
     * @param DistrictRepository $districtRepository
     * @return void
     */
    public function injectDistrictRepository(DistrictRepository $districtRepository)
    {
        $this->districtRepository = $districtRepository;
    }

    /**
     * inject categoryRepository
     *
     * @param CategoryRepository $categoryRepository
     * @return void
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * inject feUserRepository
     *
     * @param FeUserRepository $feUserRepository
     * @return void
     */
    public function injectFeUserRepository(FeUserRepository $feUserRepository)
    {
        $this->feUserRepository = $feUserRepository;
    }

    /**
     * inject session
     *
     * @param Session $session
     * @return void
     */
    public function injectSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * inject geocodeUtility
     *
     * @param GeocodeUtility $geocodeUtility
     * @return void
     */
    public function injectGeocodeUtility(GeocodeUtility $geocodeUtility)
    {
        $this->geocodeUtility = $geocodeUtility;
    }

    /**
     * PreProcessing of all actions
     *
     * @return void
     */
    public function initializeAction()
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
     * get an array with letters as keys for the glossar
     *
     * @param boolean $isWsp
     * @return array Array with starting letters as keys
     */
    protected function getGlossar($isWsp)
    {
        $glossar = [];
        $availableLetters = $this->companyRepository->getStartingLetters($isWsp);
        $possibleLetters = GeneralUtility::trimExplode(',', $this->letters);

        // add all letters which we have found in DB
        foreach ($availableLetters as $availableLetter) {
            if (MathUtility::canBeInterpretedAsInteger($availableLetter['letter'])) {
                $availableLetter['letter'] = '0-9';
            }
            // add only letters which are valid (do not add "§$%")
            if (in_array($availableLetter['letter'], $possibleLetters, true)) {
                $glossar[$availableLetter['letter']] = true;
            }
        }

        // add all valid letters which are not set/found by previous foreach
        foreach ($possibleLetters as $possibleLetter) {
            if (!array_key_exists($possibleLetter, $glossar)) {
                $glossar[$possibleLetter] = false;
            }
        }

        ksort($glossar, SORT_STRING);

        return $glossar;
    }

    /**
     * This is a workaround to help controller actions to find (hidden) companies
     *
     * @param $argumentName
     */
    protected function registerCompanyFromRequest($argumentName)
    {
        $argument = $this->request->getArgument($argumentName);
        if (is_array($argument)) {
            // get company from form ($_POST)
            $company = $this->companyRepository->findHiddenEntryByUid($argument['__identity']);
        } else {
            // get company from UID
            $company = $this->companyRepository->findHiddenEntryByUid($argument);
        }
        $this->session->registerObject($company, $company->getUid());
    }

    /**
     * A template method for displaying custom error flash messages, or to
     * display no flash message at all on errors. Override this to customize
     * the flash message in your action controller.
     *
     * @return string The flash message or FALSE if no flash message should be set
     * @api
     */
    protected function getErrorFlashMessage()
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
     * remove empty arguments from request
     *
     * @return void
     */
    protected function removeEmptyArgumentsFromRequest()
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
     * @return void
     */
    protected function deleteUploadedFilesOnValidationErrors($argument)
    {
        if ($this->getControllerContext()->getRequest()->hasArgument($argument)) {
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
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function addNewPoiCollectionToCompany(Company $company)
    {
        $response = $this->geocodeUtility->findPositionByAddress($company->getAddress());
        /* @var \JWeiland\Maps2\Domain\Model\RadiusResult $location */
        if (
            $response instanceof ObjectStorage
            && ($location = $response->current())
            && $location instanceof RadiusResult
        ) {
            /** @var PoiCollection $poiCollection */
            $poiCollection = $this->objectManager->get(PoiCollection::class);
            $poiCollection->setCollectionType('Point');
            $poiCollection->setTitle($company->getCompany());
            $poiCollection->setLatitude($location->getGeometry()->getLocation()->getLatitude());
            $poiCollection->setLongitude($location->getGeometry()->getLocation()->getLongitude());
            $poiCollection->setAddress($location->getFormattedAddress());
            $company->setTxMaps2Uid($poiCollection);
        } else {
            DebuggerUtility::var_dump($response);
            throw new \Exception('Can\'t find a result for address: ' . $company->getAddress() . '. Activate Debugging for a more detailed output.', 1465474954);
        }
    }
}
