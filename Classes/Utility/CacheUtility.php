<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\CacheTag;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Cache Utility class
 */
class CacheUtility
{
    /**
     * Adds cache tags to page cache by event-records.
     * Following cache tags will be added to TSFE:
     * "tx_yellowpages2_uid_[company:uid]"
     */
    public static function addCacheTagsByCompanyRecords(ServerRequestInterface $request, array $companyRecords): void
    {
        if (!self::getApplicationType()->isFrontend()) {
            return;
        }

        $cacheTags = [];
        foreach ($companyRecords as $companyRecord) {
            // cache tag for each companyRecord record
            $cacheTags[] = new CacheTag('tx_yellowpages2_uid_' . $companyRecord->getUid());

            if ($companyRecord->_getProperty('_localizedUid')) {
                $cacheTags[] = new CacheTag('tx_yellowpages2_uid_' . $companyRecord->_getProperty('_localizedUid'));
            }
        }

        if (count($cacheTags) > 0) {
            $request->getAttribute('frontend.cache.collector')->addCacheTags(...$cacheTags);
        }
    }

    /**
     * Adds page cache tags by used storagePages.
     * This adds tags with the scheme tx_yellowpages2_pid_[company:pid]
     */
    public static function addPageCacheTagsByQuery(ServerRequestInterface $request, QueryInterface $query): void
    {
        if (!self::getApplicationType()->isFrontend()) {
            return;
        }

        $cacheTags = [];
        if ($query->getQuerySettings()->getStoragePageIds()) {
            // Add cache tags for each storage page
            foreach ($query->getQuerySettings()->getStoragePageIds() as $pageId) {
                $cacheTags[] = new CacheTag('tx_yellowpages2_pid_' . $pageId);
            }
        } else {
            $cacheTags[] = new CacheTag('tx_yellowpages2_domain_model_company');
        }

        $request->getAttribute('frontend.cache.collector')->addCacheTags(...$cacheTags);
    }

    protected static function getApplicationType(): ApplicationType
    {
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST']);
    }
}
