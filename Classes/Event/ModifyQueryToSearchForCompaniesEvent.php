<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Event;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Modify QueryResult in CompanyRepository to search for companies by various constraints
 */
class ModifyQueryToSearchForCompaniesEvent
{
    protected QueryResultInterface $queryResult;

    protected string $searchWord;

    protected int $categoryUid;

    protected array $settings;

    public function __construct(QueryResultInterface $queryResult, string $searchWord, int $categoryUid, array $settings)
    {
        $this->queryResult = $queryResult;
        $this->searchWord = $searchWord;
        $this->categoryUid = $categoryUid;
        $this->settings = $settings;
    }

    public function getQueryResult(): QueryResultInterface
    {
        return $this->queryResult;
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
