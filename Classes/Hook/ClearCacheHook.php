<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Hook;

use TYPO3\CMS\Core\Cache\CacheManager;

/**
 * Hook into DataHandler and clear special caches after saving a company.
 */
class ClearCacheHook
{
    protected CacheManager $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Flushes the cache if a company record was edited.
     * This happens on two levels: by UID and by PID.
     */
    public function clearCachePostProc(array $params): void
    {
        if (isset($params['table']) && $params['table'] === 'tx_yellowpages2_domain_model_company') {
            $cacheTagsToFlush = ['tx_yellowpages2_domain_model_company'];
            if (isset($params['uid'])) {
                $cacheTagsToFlush[] = 'tx_yellowpages2_uid_' . $params['uid'];
            }

            if (isset($params['uid_page'])) {
                $cacheTagsToFlush[] = 'tx_yellowpages2_pid_' . $params['uid_page'];
            }

            foreach ($cacheTagsToFlush as $cacheTag) {
                $this->cacheManager->flushCachesInGroupByTag('pages', $cacheTag);
            }
        }
    }
}
