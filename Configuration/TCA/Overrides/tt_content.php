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
    'yellowpages2',
    'Directory',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.directory.title',
    'ext-yellowpages2-directory-wizard-icon',
    'plugins',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.directory.description',
);

// registerPlugin() creates $TCA['tt_content']['types']['yellowpages2_directory'] on the fly, so
// addToAllTCAtypes() - which only touches already existing types - must run after it, not before.
ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:plugin,pi_flexform, pages, recursive',
    'yellowpages2_directory',
    'after:subheader',
);

ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:yellowpages2/Configuration/FlexForms/YellowPages.xml',
    'yellowpages2_directory',
);

ExtensionUtility::registerPlugin(
    'yellowpages2',
    'Management',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.management.title',
    'ext-yellowpages2-directory-wizard-icon',
    'plugins',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.management.description',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:plugin,pi_flexform, pages, recursive',
    'yellowpages2_management',
    'after:subheader',
);

ExtensionUtility::registerPlugin(
    'yellowpages2',
    'Search',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.search.title',
    'ext-yellowpages2-directory-wizard-icon',
    'plugins',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.search.description',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:plugin,pi_flexform, pages, recursive',
    'yellowpages2_search',
    'after:subheader',
);

ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:yellowpages2/Configuration/FlexForms/Search.xml',
    'yellowpages2_search',
);

$GLOBALS['TCA']['tt_content']['types']['yellowpages2_directory']['previewRenderer'] = Yellowpages2PluginPreview::class;
