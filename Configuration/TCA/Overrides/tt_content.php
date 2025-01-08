<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

use JWeiland\Yellowpages2\Backend\Preview\Yellowpages2PluginPreview;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['yellowpages2_directory'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['yellowpages2_directory'] = 'select_key';

ExtensionUtility::registerPlugin(
    'yellowpages2',
    'Directory',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.title',
    'ext-yellowpages2-directory-wizard-icon',
    'plugins',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.directory.description',
);

ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:yellowpages2/Configuration/FlexForms/YellowPages.xml',
    'yellowpages2_directory',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;Configuration,pi_flexform, pages, select_key, recursive',
    'yellowpages2_directory',
    'after:subheader',
);

$GLOBALS['TCA']['tt_content']['types']['yellowpages2_directory']['previewRenderer'] = Yellowpages2PluginPreview::class;
