<?php

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'frontend' => [
        'jweiland/yellowpages2/company-create' => [
            'target' => \JWeiland\Yellowpages2\Middleware\CompanyCreateActionMiddleware::class,
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
        ],
        'jweiland/yellowpages2/company-search' => [
            'target' => \JWeiland\Yellowpages2\Middleware\SearchActionSanitizeMiddleware::class,
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
        ],
    ],
];
