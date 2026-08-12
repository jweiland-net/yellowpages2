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
 * Modify QueryResult in CompanyRepository to find company by letter
 */
final readonly class ModifyQueryToFindCompanyByLetterEvent
{
    public function __construct(
        private QueryResultInterface $queryResult,
        private array $settings,
    ) {}

    public function getQueryResult(): QueryResultInterface
    {
        return $this->queryResult;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
