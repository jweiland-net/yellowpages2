<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Utility;

use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/*
 * Cache Utility class
 */
class CacheUtility
{
    /**
     * Adds cache tags to page cache by event-records.
     * Following cache tags will be added to TSFE:
     * "tx_yellowpages2_uid_[company:uid]"
     */
    public static function addCacheTagsByCompanyRecords(array $companyRecords): void
    {
        if (!self::getApplicationType()->isFrontend()) {
            return;
        }

        $cacheTags = [];
        foreach ($companyRecords as $companyRecord) {
            // cache tag for each companyRecord record
            $cacheTags[] = 'tx_yellowpages2_uid_' . $companyRecord->getUid();

            if ($companyRecord->_getProperty('_localizedUid')) {
                $cacheTags[] = 'tx_yellowpages2_uid_' . $companyRecord->_getProperty('_localizedUid');
            }
        }
        if (count($cacheTags) > 0) {
            $GLOBALS['TSFE']->addCacheTags($cacheTags);
        }
    }

    /**
     * Adds page cache tags by used storagePages.
     * This adds tags with the scheme tx_yellowpages2_pid_[company:pid]
     */
    public static function addPageCacheTagsByQuery(QueryInterface $query): void
    {
        if (!self::getApplicationType()->isFrontend()) {
            return;
        }

        $cacheTags = [];
        if ($query->getQuerySettings()->getStoragePageIds()) {
            // Add cache tags for each storage page
            foreach ($query->getQuerySettings()->getStoragePageIds() as $pageId) {
                $cacheTags[] = 'tx_yellowpages2_pid_' . $pageId;
            }
        } else {
            $cacheTags[] = 'tx_yellowpages2_domain_model_company';
        }
        $GLOBALS['TSFE']->addCacheTags($cacheTags);
    }

    protected static function getApplicationType(): ApplicationType
    {
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST']);
    }
}
