<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

if (!defined('TYPO3')) {
    die('Access denied.');
}

use JWeiland\Yellowpages2\Backend\Preview\Yellowpages2PluginPreview;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::registerPlugin(
    'Yellowpages2',
    'Directory',
    'yellowpages2.db:plugin.directory.title',
    'ext-yellowpages2-directory-wizard-icon',
    'plugins',
    'yellowpages2.db:plugin.directory.description',
    'FILE:EXT:yellowpages2/Configuration/FlexForms/YellowPages.xml',
);

ExtensionUtility::registerPlugin(
    'Yellowpages2',
    'Management',
    'yellowpages2.db:plugin.management.title',
    'ext-yellowpages2-directory-wizard-icon',
    'plugins',
    'yellowpages2.db:plugin.management.description',
);

ExtensionUtility::registerPlugin(
    'Yellowpages2',
    'Search',
    'yellowpages2.db:plugin.search.title',
    'ext-yellowpages2-directory-wizard-icon',
    'plugins',
    'yellowpages2.db:plugin.search.description',
    'FILE:EXT:yellowpages2/Configuration/FlexForms/Search.xml',
);

// The FlexForm tab incl. pi_flexform is added by registerPlugin()
ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'pages, recursive',
    'yellowpages2_directory',
    'after:pi_flexform',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'pages, recursive',
    'yellowpages2_management',
    'after:subheader',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'pages, recursive',
    'yellowpages2_search',
    'after:pi_flexform',
);

$GLOBALS['TCA']['tt_content']['types']['yellowpages2_directory']['previewRenderer'] = Yellowpages2PluginPreview::class;
$GLOBALS['TCA']['tt_content']['types']['yellowpages2_search']['previewRenderer'] = Yellowpages2PluginPreview::class;
