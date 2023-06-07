<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Repository;

use JWeiland\Yellowpages2\Persistence\Query;
use JWeiland\Yellowpages2\Persistence\QueryResult;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Abstract repository to override some extbase repository methods
 */
abstract class AbstractRepository extends Repository
{
    /**
     * Returns all objects of this repository.
     *
     * @return QueryResult|array
     */
    public function findAll()
    {
        return $this->createContentObjectQuery()->execute();
    }

    /**
     * Returns a query for objects of this repository
     */
    public function createContentObjectQuery(): Query
    {
        $query = GeneralUtility::makeInstance(Query::class);
        $query->setType($this->objectType);

        $querySettings = GeneralUtility::makeInstance(QuerySettingsInterface::class);
        $frameworkConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
        $querySettings->setStoragePageIds(
            GeneralUtility::intExplode(',', $frameworkConfiguration['persistence']['storagePid'] ?? '')
        );
        $query->setQuerySettings($querySettings);

        if ($this->defaultOrderings !== []) {
            $query->setOrderings($this->defaultOrderings);
        }

        if ($this->defaultQuerySettings !== null) {
            $query->setQuerySettings(clone $this->defaultQuerySettings);
        }

        return $query;
    }
}
