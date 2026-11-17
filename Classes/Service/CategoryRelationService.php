<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Service;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Reads and writes the category relations (sys_category_record_mm) of a
 * company, like the DataHandler does for the MM columns "main_trade" and
 * "trades". The column of the company contains the amount of relations.
 */
readonly class CategoryRelationService
{
    private const TABLE = 'tx_yellowpages2_domain_model_company';

    private const MM_TABLE = 'sys_category_record_mm';

    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    /**
     * @return int[] UIDs of the related categories in the stored order
     */
    public function getCategoryUids(int $companyUid, string $column): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::MM_TABLE);
        $result = $queryBuilder
            ->select('uid_local')
            ->from(self::MM_TABLE)
            ->where(
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($companyUid, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter(self::TABLE)),
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter($column)),
            )
            ->orderBy('sorting_foreign')
            ->executeQuery();

        return array_map(intval(...), $result->fetchFirstColumn());
    }

    /**
     * Replaces all category relations of the company in the given column
     *
     * @param int[] $categoryUids
     */
    public function setCategoryUids(int $companyUid, string $column, array $categoryUids): void
    {
        $categoryUids = array_values(array_unique($categoryUids));
        $connection = $this->connectionPool->getConnectionForTable(self::MM_TABLE);
        $connection->delete(self::MM_TABLE, [
            'uid_foreign' => $companyUid,
            'tablenames' => self::TABLE,
            'fieldname' => $column,
        ]);

        foreach ($categoryUids as $sorting => $categoryUid) {
            $connection->insert(self::MM_TABLE, [
                'uid_local' => $categoryUid,
                'uid_foreign' => $companyUid,
                'tablenames' => self::TABLE,
                'fieldname' => $column,
                'sorting_foreign' => $sorting + 1,
            ]);
        }

        $this->connectionPool->getConnectionForTable(self::TABLE)
            ->update(self::TABLE, [$column => count($categoryUids)], ['uid' => $companyUid]);
    }
}
