<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Service;

use JWeiland\Yellowpages2\Form\CompanyFormData;
use JWeiland\Yellowpages2\Form\PoiPosition;
use JWeiland\Yellowpages2\Helper\PathSegmentHelper;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

/**
 * Stores the company of the frontend form incl. its category relations and
 * file references and the position of its POI collection (EXT:maps2). A
 * new company gets the current frontend user as owner and a path segment. An edited company keeps its "pid", owner and path
 * segment.
 */
readonly class CompanyRecordService
{
    public const TABLE = 'tx_yellowpages2_domain_model_company';

    /**
     * These columns are set for new companies only
     */
    private const INSERT_ONLY_COLUMNS = ['pid', 'crdate', 'fe_user', 'path_segment'];

    public function __construct(
        private ConnectionPool $connectionPool,
        private PathSegmentHelper $pathSegmentHelper,
        private CategoryRelationService $categoryRelationService,
        private FileReferenceService $fileReferenceService,
        private CompanyPoiCollectionService $companyPoiCollectionService,
    ) {}

    /**
     * @param int $companyUid UID of the company to update. 0 for new companies.
     * @param int $frontendUserUid Owner of a new company
     * @return int UID of the stored company
     */
    public function save(CompanyFormData $companyFormData, int $companyUid, int $frontendUserUid): int
    {
        $storedCompany = $this->findCompany($companyUid);
        $companyUid = $storedCompany === []
            ? $this->insert($companyFormData->columns, $frontendUserUid)
            : $this->update($companyFormData->columns, $companyUid);
        $company = $this->findCompany($companyUid);

        foreach ($companyFormData->categories as $column => $categoryUids) {
            $this->categoryRelationService->setCategoryUids($companyUid, $column, $categoryUids);
        }
        foreach ($companyFormData->files as $column => $fileUids) {
            $this->fileReferenceService->setFileUids($companyUid, (int)$company['pid'], $column, $fileUids);
        }

        if ($companyFormData->position instanceof PoiPosition) {
            $this->companyPoiCollectionService->savePosition($company, $companyFormData->position);
        }

        return $companyUid;
    }

    /**
     * Hidden companies are found, too. Deleted ones not.
     *
     * @return array<string, mixed>
     */
    public function findCompany(int $companyUid): array
    {
        if ($companyUid <= 0) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());
        $company = $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($companyUid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        return is_array($company) ? $company : [];
    }

    /**
     * @param array<string, int|string> $columns
     */
    private function insert(array $columns, int $frontendUserUid): int
    {
        $columns = array_replace($columns, [
            'crdate' => time(),
            'tstamp' => time(),
            'fe_user' => $frontendUserUid,
        ]);

        $connection = $this->connectionPool->getConnectionForTable(self::TABLE);
        $connection->insert(self::TABLE, $columns);
        $companyUid = (int)$connection->lastInsertId();
        $this->addPathSegment($companyUid, (string)($columns['company'] ?? ''), (int)($columns['pid'] ?? 0));

        return $companyUid;
    }

    /**
     * @param array<string, int|string> $columns
     */
    private function update(array $columns, int $companyUid): int
    {
        $columns = array_diff_key($columns, array_flip(self::INSERT_ONLY_COLUMNS));
        $columns['tstamp'] = time();

        $this->connectionPool->getConnectionForTable(self::TABLE)
            ->update(self::TABLE, $columns, ['uid' => $companyUid]);

        return $companyUid;
    }

    /**
     * The UID is part of the path segment, so it is added after the insert
     */
    private function addPathSegment(int $companyUid, string $companyName, int $pid): void
    {
        $pathSegment = $this->pathSegmentHelper->generatePathSegment(
            ['uid' => $companyUid, 'pid' => $pid, 'company' => $companyName],
            $pid,
        );

        $this->connectionPool->getConnectionForTable(self::TABLE)
            ->update(self::TABLE, ['path_segment' => $pathSegment], ['uid' => $companyUid]);
    }
}
