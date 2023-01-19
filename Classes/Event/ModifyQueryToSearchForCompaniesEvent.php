<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Event;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * Modify query in CompanyRepository to search for companies by various constraints
 */
class ModifyQueryToSearchForCompaniesEvent
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var string
     */
    protected $searchWord;

    /**
     * @var int
     */
    protected $categoryUid;

    /**
     * @var array
     */
    protected $settings;

    public function __construct(QueryBuilder $queryBuilder, string $searchWord, int $categoryUid, array $settings)
    {
        $this->queryBuilder = $queryBuilder;
        $this->searchWord = $searchWord;
        $this->categoryUid = $categoryUid;
        $this->settings = $settings;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getSearchWord(): string
    {
        return $this->searchWord;
    }

    public function getCategoryUid(): int
    {
        return $this->categoryUid;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
