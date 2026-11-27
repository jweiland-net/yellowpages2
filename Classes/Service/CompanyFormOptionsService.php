<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Service;

use Doctrine\DBAL\ArrayParameterType;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

/**
 * Provides the options of the selects and checkboxes of the company form:
 * The districts of the storage pages and the child categories of a parent
 * category. Records are translated to the current language. Options are
 * sorted by their label.
 */
final readonly class CompanyFormOptionsService
{
    private const DISTRICT_TABLE = 'tx_yellowpages2_domain_model_district';

    private const CATEGORY_TABLE = 'sys_category';

    public function __construct(
        private ConnectionPool $connectionPool,
        private PageRepository $pageRepository,
    ) {}

    /**
     * @param int[] $storagePids All districts, if empty
     * @return array<int, string>
     */
    public function getDistricts(array $storagePids): array
    {
        $queryBuilder = $this->getQueryBuilder(self::DISTRICT_TABLE);
        if ($storagePids !== []) {
            $queryBuilder->andWhere($queryBuilder->expr()->in(
                'pid',
                $queryBuilder->createNamedParameter($storagePids, ArrayParameterType::INTEGER),
            ));
        }

        return $this->getOptions($queryBuilder, self::DISTRICT_TABLE, 'district');
    }

    /**
     * @return array<int, string>
     */
    public function getCategories(int $parentCategoryUid): array
    {
        $queryBuilder = $this->getQueryBuilder(self::CATEGORY_TABLE);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(
            'parent',
            $queryBuilder->createNamedParameter($parentCategoryUid, Connection::PARAM_INT),
        ));

        return $this->getOptions($queryBuilder, self::CATEGORY_TABLE, 'title');
    }

    /**
     * @return array<int, string>
     */
    private function getOptions(QueryBuilder $queryBuilder, string $table, string $labelColumn): array
    {
        $options = [];
        foreach ($queryBuilder->executeQuery()->fetchAllAssociative() as $record) {
            $translatedRecord = $this->pageRepository->getLanguageOverlay($table, $record);
            if (is_array($translatedRecord)) {
                $options[(int)$record['uid']] = (string)$translatedRecord[$labelColumn];
            }
        }

        asort($options, SORT_LOCALE_STRING);

        return $options;
    }

    /**
     * Records in default language and for all languages
     */
    private function getQueryBuilder(string $table): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->setRestrictions(new FrontendRestrictionContainer());

        return $queryBuilder
            ->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->in(
                'sys_language_uid',
                $queryBuilder->createNamedParameter([0, -1], ArrayParameterType::INTEGER),
            ));
    }
}
