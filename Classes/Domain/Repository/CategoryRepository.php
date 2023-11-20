<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Repository;

use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Traits\GetLanguageStatementTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Repository for TYPO3 categories
 *
 * @method QueryResultInterface findByParent(int $categoryUid)
 */
class CategoryRepository extends Repository
{
    use GetLanguageStatementTrait;

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

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
     * Needed for search partial to just list categories which have a company record assigned.
     * There is no possibility to get all categories which are assigned to a company with extbase
     * query (col "items" may contain various kinds of records). That's why we have to use the
     * Doctrine QueryBuilder here.
     */
    public function findRelated(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(true);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_yellowpages2_domain_model_company');
        $queryBuilder
            // Keep '*' for workspaces and translation
            ->select('*')
            ->from('tx_yellowpages2_domain_model_company', 'tx_yellowpages2_domain_model_company')
            ->leftJoin(
                'tx_yellowpages2_domain_model_company',
                'sys_category_record_mm',
                'sys_category_record_mm',
                (string)$queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        'sys_category_record_mm.tablenames',
                        $queryBuilder->createNamedParameter('tx_yellowpages2_domain_model_company')
                    ),
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->eq(
                            'sys_category_record_mm.fieldname',
                            $queryBuilder->createNamedParameter('main_trade')
                        ),
                        $queryBuilder->expr()->eq(
                            'sys_category_record_mm.fieldname',
                            $queryBuilder->createNamedParameter('trades')
                        ),
                    ),
                    $queryBuilder->expr()->eq(
                        'sys_category_record_mm.uid_foreign',
                        $queryBuilder->quoteIdentifier('tx_yellowpages2_domain_model_company.uid')
                    )
                )
            )
            ->leftJoin(
                'sys_category_record_mm',
                'sys_category',
                'sys_category',
                $queryBuilder->expr()->eq(
                    'sys_category_record_mm.uid_local',
                    $queryBuilder->quoteIdentifier('sys_category.uid')
                )
            )
            ->where(
                $queryBuilder->expr()->isNotNull(
                    'sys_category.uid'
                ),
                $queryBuilder->expr()->in(
                    'tx_yellowpages2_domain_model_company.pid',
                    $this->getQuerySettingsOfCompany()->getStoragePageIds()
                )
            )
            ->andWhere(
                $queryBuilder->expr()->orX(
                    ...$this->getLanguageStatement(
                        'tx_yellowpages2_domain_model_company',
                    'tx_yellowpages2_domain_model_company',
                        $this->getQuerySettingsOfCompany(),
                        $queryBuilder
                    )
                )
            )
            ->orderBy('sys_category.title', 'ASC')
            ->groupBy('sys_category.uid');

        return $query->statement($queryBuilder)->execute();
    }

    /**
     * Get QuerySettings for company records. Needed for translation and to select companies by requested PID.
     */
    protected function getQuerySettingsOfCompany(): QuerySettingsInterface
    {
        return $this->companyRepository->createQuery()->getQuerySettings();
    }
}
