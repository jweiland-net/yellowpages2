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
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.directory.title',
    'ext-yellowpages2-directory-wizard-icon',
    'plugins',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.directory.description',
);

ExtensionUtility::registerPlugin(
    'Yellowpages2',
    'Management',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.management.title',
    'ext-yellowpages2-directory-wizard-icon',
    'plugins',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.management.description',
);

ExtensionUtility::registerPlugin(
    'Yellowpages2',
    'Search',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.search.title',
    'ext-yellowpages2-directory-wizard-icon',
    'plugins',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.search.description',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:plugin,pi_flexform, pages, recursive',
    'yellowpages2_directory',
    'after:subheader',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'pages, recursive',
    'yellowpages2_management',
    'after:subheader',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:plugin,pi_flexform, pages, recursive',
    'yellowpages2_search',
    'after:subheader',
);

// pi_flexform is already added to showitem above, so the data structures are assigned directly
$GLOBALS['TCA']['tt_content']['types']['yellowpages2_directory']['columnsOverrides']['pi_flexform']['config']['ds']
    = 'FILE:EXT:yellowpages2/Configuration/FlexForms/YellowPages.xml';
$GLOBALS['TCA']['tt_content']['types']['yellowpages2_search']['columnsOverrides']['pi_flexform']['config']['ds']
    = 'FILE:EXT:yellowpages2/Configuration/FlexForms/Search.xml';

$GLOBALS['TCA']['tt_content']['types']['yellowpages2_directory']['previewRenderer'] = Yellowpages2PluginPreview::class;
$GLOBALS['TCA']['tt_content']['types']['yellowpages2_search']['previewRenderer'] = Yellowpages2PluginPreview::class;
