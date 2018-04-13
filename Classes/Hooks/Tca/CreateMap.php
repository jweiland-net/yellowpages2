<?php
declare(strict_types=1);
namespace JWeiland\Yellowpages2\Hooks\Tca;

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

use JWeiland\Maps2\Domain\Model\Location;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\RadiusResult;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Utility\GeocodeUtility;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CreateMap
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var GeocodeUtility
     */
    protected $geocodeUtility;

    /**
     * @var array
     */
    protected $currentRecord = [];

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

    /**
     * @var PoiCollectionRepository
     */
    protected $poiCollectionRepository;

    /**
     * initializes this object
     *
     * @return void
     */
    public function init()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->geocodeUtility = $this->objectManager->get(GeocodeUtility::class);

        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $this->companyRepository = $this->objectManager->get(CompanyRepository::class);
        $this->poiCollectionRepository = $this->objectManager->get(PoiCollectionRepository::class);
    }

    /**
     * try to find a similar poiCollection. If found connect it with current record
     *
     * @param string $status "new" od something else to update the record
     * @param string $table The table name
     * @param int $uid The UID of the new or updated record. Can be prepended with NEW if record is new. Use: $this->substNEWwithIDs to convert
     * @param array $fieldArray The fields of the current record
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     * @return void
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $uid, array $fieldArray, DataHandler $pObj)
    {
        // process this hook only on expected table
        if ($table !== 'tx_yellowpages2_domain_model_company') {
            return;
        }

        $this->init();

        if ($status === 'new') {
            $uid = current($pObj->substNEWwithIDs);
        }

        $this->currentRecord = $this->getFullRecord($table, $uid);

        // create new map-record and set them in relation
        $response = $this->geocodeUtility->findPositionByAddress($this->getAddress());

        if ($response instanceof ObjectStorage && $response->count()) {
            /** @var RadiusResult $firstResult */
            $firstResult = $response->current();
            $location = $firstResult->getGeometry()->getLocation();
            $address = $firstResult->getFormattedAddress();
            $poiUid = $this->createNewPoiCollection($location, $address);
            $this->updateCurrentRecord($poiUid);
        }
    }

    /**
     * get full yellowpages record
     * While updating a record only the changed fields will be in $fieldArray
     *
     * @param string $table
     * @param int $uid
     * @return array|null
     */
    public function getFullRecord($table, $uid)
    {
        return BackendUtility::getRecord($table, $uid);
    }

    /**
     * get address for google search
     *
     * @return string Prepared address for URI
     */
    public function getAddress(): string
    {
        $address = [];
        $address[] = $this->currentRecord['street'];
        $address[] = $this->currentRecord['house_number'];
        $address[] = $this->currentRecord['zip'];
        $address[] = $this->currentRecord['city'];
        $address[] = 'Deutschland';

        return implode(' ', $address);
    }

    /**
     * update yellowpages record
     *
     * @param int $poi
     * @return void
     */
    public function updateCurrentRecord(int $poi)
    {
        /** @var Company $company */
        $company = $this->companyRepository->findByUid($this->currentRecord['uid']);
        /** @var PoiCollection $poiCollection */
        $poiCollection = $this->poiCollectionRepository->findByUid($poi);

        $company->setTxMaps2Uid($poiCollection);

        $this->companyRepository->update($company);

        $this->persistenceManager->persistAll();

        $this->currentRecord['tx_maps2_uid'] = $poi;
    }

    /**
     * creates a new poiCollection before updating the current yellowPages record
     *
     * @param Location $location
     * @param string $address Formatted Address returned from Google
     *
     * @return int insert UID
     */
    public function createNewPoiCollection(Location $location, $address): int
    {
        $tsConfig = $this->getTsConfig();

        $poiCollection = new PoiCollection();
        $poiCollection->setPid((int)$tsConfig['pid']);
        $poiCollection->setLatitude($location->getLatitude());
        $poiCollection->setLongitude($location->getLongitude());
        $poiCollection->setCollectionType('Point');
        $poiCollection->setTitle($this->currentRecord['company']);
        $poiCollection->setAddress($address);

        $this->poiCollectionRepository->add($poiCollection);

        $this->persistenceManager->persistAll();

        return $poiCollection->getUid();
    }

    /**
     * get TSconfig
     *
     * @return array
     * @throws \Exception
     */
    public function getTsConfig(): array
    {
        $tsConfig = BackendUtility::getModTSconfig($this->currentRecord['uid'], 'ext.yellowpages2');
        if (is_array($tsConfig) && isset($tsConfig['properties']['pid'])) {
            return $tsConfig['properties'];
        }
        throw new \Exception('no PID for maps2 given. Please add this PID in extension configuration of yellowpages2 or set it in page TSconfig', 1364889195);
    }
}
