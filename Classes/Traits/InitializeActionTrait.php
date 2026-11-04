<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Traits;

trait InitializeActionTrait
{
    public function initializeAction(): void
    {
        // If these values were not set, they will be filled with 0.
        // But that is not good, because UriBuilder accepts 0 as pid,
        // so it's better to set them to NULL.
        $pageSettings = [
            'pidOfDetailPage',
            'pidOfListPage',
            'pidOfManagementPage',
            'pidOfSearchResults',
        ];

        foreach ($pageSettings as $pageSetting) {
            if (empty($this->settings[$pageSetting])) {
                $this->settings[$pageSetting] = null;
            }
        }
    }
}
