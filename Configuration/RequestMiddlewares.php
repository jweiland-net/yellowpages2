<?php

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'frontend' => [
        'jweiland/yellowpages2/controller-actions-middleware' => [
            'target' => \JWeiland\Yellowpages2\Middleware\ControllerActionsMiddleware::class,
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
        ],
    ],
];
