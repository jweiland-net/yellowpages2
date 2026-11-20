<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use JWeiland\Yellowpages2\Domain\Model\Category;
use JWeiland\Yellowpages2\Domain\Traits\GetLanguageStatementTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for TYPO3 categories
 *
 * @extends Repository<Category>
 * @method QueryResultInterface<int, Category> findByParent(int $categoryUid)
 */
class CategoryRepository extends Repository
{
    use GetLanguageStatementTrait;

    private const COMPANY_TABLE = 'tx_yellowpages2_domain_model_company';

    /**
     * @var array<non-empty-string, QueryInterface::ORDER_*>
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    protected CompanyRepository $companyRepository;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
        parent::__construct();
    }

    public function injectCompanyRepository(CompanyRepository $companyRepository): void
    {
        $this->companyRepository = $companyRepository;
    }

    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);

        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Needed for the search partial to list categories, which are assigned as
     * main trade or trade to a company, only. There is no possibility to get
     * these categories with an Extbase query (column "items" may contain
     * various kinds of records). That's why the Doctrine QueryBuilder is used.
     * The categories are selected with a sub query, so the query is valid
     * with sql_mode "only_full_group_by" and on all DBMS.
     *
     * @return QueryResultInterface<int, Category>
     */
    public function findRelated(): QueryResultInterface
    {
        /** @var Query<Category> $query */
        $query = $this->createQuery();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            // Keep '*' for workspaces and translation
            ->select('sys_category.*')
            ->from('sys_category')
            ->where($queryBuilder->expr()->in(
                'sys_category.uid',
                $this->getSqlOfCategoriesOfCompanies($queryBuilder),
            ))
            ->orderBy('sys_category.title', 'ASC');

        /** @var QueryResultInterface<int, Category> $result */
        $result = $query->statement($queryBuilder)->execute();

        return $result;
    }

    /**
     * UIDs of the categories, which are assigned as main trade or trade to
     * the visible companies of the storage pages in the current language.
     * Parameters are created at the outer query builder.
     */
    protected function getSqlOfCategoriesOfCompanies(QueryBuilder $queryBuilder): string
    {
        $querySettings = $this->getQuerySettingsOfCompany();
        $subQueryBuilder = $this->connectionPool->getQueryBuilderForTable(self::COMPANY_TABLE);
        $subQueryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $subQueryBuilder
            ->select('mm.uid_local')
            ->from(self::COMPANY_TABLE, 'company')
            ->join('company', 'sys_category_record_mm', 'mm', (string)$subQueryBuilder->expr()->and(
                $subQueryBuilder->expr()->eq('mm.uid_foreign', $subQueryBuilder->quoteIdentifier('company.uid')),
                $subQueryBuilder->expr()->eq('mm.tablenames', $queryBuilder->createNamedParameter(self::COMPANY_TABLE)),
                $subQueryBuilder->expr()->in('mm.fieldname', $queryBuilder->createNamedParameter(
                    ['main_trade', 'trades'],
                    ArrayParameterType::STRING,
                )),
            ))
            ->where(
                $subQueryBuilder->expr()->in('company.pid', $queryBuilder->createNamedParameter(
                    $querySettings->getStoragePageIds(),
                    ArrayParameterType::INTEGER,
                )),
                $subQueryBuilder->expr()->or(
                    $this->getLanguageStatement(self::COMPANY_TABLE, 'company', $querySettings, $subQueryBuilder),
                ),
            );

        return $subQueryBuilder->getSQL();
    }

    /**
     * Get QuerySettings for company records. Needed for translation and to select companies by requested PID.
     */
    protected function getQuerySettingsOfCompany(): QuerySettingsInterface
    {
        return $this->companyRepository->createQuery()->getQuerySettings();
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        return $queryBuilder;
    }
}
