<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'ext-yellowpages2-directory-wizard-icon' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:yellowpages2/Resources/Public/Icons/plugin_wizard.svg',
    ],
];
