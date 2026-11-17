<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Service;

use JWeiland\Yellowpages2\Configuration\ExtConf;
use JWeiland\Yellowpages2\Form\PoiPosition;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

/**
 * Reads and writes the POI collection (EXT:maps2) of a company. The
 * position is the one of the map step of the company form, which the user
 * may have corrected. A company without POI collection gets a new one of
 * type "Point", which is stored in the folder of the extension setting
 * "poiCollectionPid" or else next to the company.
 */
readonly class CompanyPoiCollectionService
{
    private const TABLE = 'tx_yellowpages2_domain_model_company';

    private const POI_COLLECTION_TABLE = 'tx_maps2_domain_model_poicollection';

    private const ADDRESS_COLUMNS = ['street', 'house_number', 'zip', 'city'];

    public function __construct(
        private ConnectionPool $connectionPool,
        private ExtConf $extConf,
    ) {}

    public function getPosition(int $poiCollectionUid): ?PoiPosition
    {
        $poiCollection = $this->findPoiCollection($poiCollectionUid);

        return $poiCollection === [] ? null : PoiPosition::fromFormValue($poiCollection);
    }

    /**
     * @param array<string, mixed> $company The saved company incl. "uid", "pid" and "tx_maps2_uid"
     */
    public function savePosition(array $company, PoiPosition $position): void
    {
        $poiCollectionUid = (int)($company['tx_maps2_uid'] ?? 0);
        $columns = [
            'tstamp' => time(),
            'title' => (string)($company['company'] ?? ''),
            'address' => $this->getAddress($company),
            'latitude' => $position->latitude,
            'longitude' => $position->longitude,
        ];

        if ($this->findPoiCollection($poiCollectionUid) !== []) {
            $this->connectionPool->getConnectionForTable(self::POI_COLLECTION_TABLE)
                ->update(self::POI_COLLECTION_TABLE, $columns, ['uid' => $poiCollectionUid]);
            return;
        }

        $this->connectionPool->getConnectionForTable(self::TABLE)->update(
            self::TABLE,
            ['tx_maps2_uid' => $this->insertPoiCollection($columns, (int)($company['pid'] ?? 0))],
            ['uid' => (int)$company['uid']],
        );
    }

    /**
     * Street, house number, zip and city of a company record, e.g.
     * "Echterdinger Straße 57, 70794 Filderstadt"
     *
     * @param array<string, mixed> $company
     */
    public function getAddress(array $company): string
    {
        $address = array_map(
            static fn(string $column): string => trim((string)($company[$column] ?? '')),
            self::ADDRESS_COLUMNS,
        );

        return trim(sprintf('%s %s, %s %s', ...$address), ' ,');
    }

    /**
     * @param array<string, int|float|string> $columns
     */
    private function insertPoiCollection(array $columns, int $companyPid): int
    {
        $connection = $this->connectionPool->getConnectionForTable(self::POI_COLLECTION_TABLE);
        $connection->insert(self::POI_COLLECTION_TABLE, array_replace($columns, [
            'pid' => $this->extConf->getPoiCollectionPid() ?: $companyPid,
            'crdate' => time(),
            'collection_type' => 'Point',
        ]));

        return (int)$connection->lastInsertId();
    }

    /**
     * @return array<string, mixed>
     */
    private function findPoiCollection(int $poiCollectionUid): array
    {
        if ($poiCollectionUid <= 0) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::POI_COLLECTION_TABLE);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());
        $poiCollection = $queryBuilder
            ->select('uid', 'latitude', 'longitude')
            ->from(self::POI_COLLECTION_TABLE)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($poiCollectionUid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        return is_array($poiCollection) ? $poiCollection : [];
    }
}
