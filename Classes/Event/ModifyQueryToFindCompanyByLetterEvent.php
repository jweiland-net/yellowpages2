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
 * Modify query in CompanyRepository to find company by letter
 */
class ModifyQueryToFindCompanyByLetterEvent
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var array
     */
    protected $settings;

    public function __construct(QueryBuilder $queryBuilder, array $settings)
    {
        $this->queryBuilder = $queryBuilder;
        $this->settings = $settings;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
