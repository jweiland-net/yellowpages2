<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for TYPO3 categories
 *
 * @method QueryResultInterface findByParent(int $categoryUid)
 */
class CategoryRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);

        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Find all sys_category records which have a relation to a company record
     */
    public function findRelated(): QueryResultInterface
    {
        $query = $this->createQuery();

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_category');
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder
            ->select('sc.*')
            ->leftJoin(
                'sc',
                'sys_category_record_mm',
                'sc_mm',
                (string)$queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        'sc_mm.tablenames',
                        $queryBuilder->createNamedParameter('tx_yellowpages2_domain_model_company')
                    ),
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->eq(
                            'sc_mm.fieldname',
                            $queryBuilder->createNamedParameter('main_trade')
                        ),
                        $queryBuilder->expr()->eq(
                            'sc_mm.fieldname',
                            $queryBuilder->createNamedParameter('main_trade')
                        ),
                    ),
                    $queryBuilder->expr()->eq(
                        'sc_mm.uid_local',
                        $queryBuilder->quoteIdentifier('sc.uid')
                    )
                )
            )
            ->join(
                'sc_mm',
                'tx_yellowpages2_domain_model_company',
                'c',
                $queryBuilder->expr()->eq(
                    'sc_mm.uid_foreign',
                    $queryBuilder->quoteIdentifier('c.uid')
                )
            )
            ->from('sys_category', 'sc')
            ->where(
                $queryBuilder->expr()->isNotNull(
                    'c.uid'
                )
            )
            ->orderBy('title');

        return $query->statement($queryBuilder)->execute();
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
