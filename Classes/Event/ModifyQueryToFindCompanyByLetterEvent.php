<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Event;

use JWeiland\Yellowpages2\Domain\Model\Company;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Modify QueryResult in CompanyRepository to find company by letter
 */
final readonly class ModifyQueryToFindCompanyByLetterEvent
{
    /**
     * @param QueryResultInterface<int, Company> $queryResult
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private QueryResultInterface $queryResult,
        private array $settings,
    ) {}

    /**
     * @return QueryResultInterface<int, Company>
     */
    public function getQueryResult(): QueryResultInterface
    {
        return $this->queryResult;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
