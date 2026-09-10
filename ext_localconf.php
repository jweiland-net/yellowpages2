<?php

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Yellowpages2\Controller\CompanyController;
use JWeiland\Yellowpages2\Controller\ManagementController;
use JWeiland\Yellowpages2\Controller\MapController;
use JWeiland\Yellowpages2\Controller\SearchController;
use JWeiland\Yellowpages2\Hook\ClearCacheHook;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

ExtensionUtility::configurePlugin(
    'yellowpages2',
    'Directory',
    [
        CompanyController::class => 'list, show',
    ],
    // non-cacheable actions
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'yellowpages2',
    'Management',
    [
        ManagementController::class => 'listMyCompanies, new, create, edit, update, activate, perform',
        MapController::class => 'new, create, edit, update, activate',
    ],
    // non-cacheable actions
    [
        ManagementController::class => 'listMyCompanies, edit, create, update, activate',
        MapController::class => 'create, update',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'yellowpages2',
    'Search',
    [
        SearchController::class => 'show, listSearchResults',
    ],
    // non-cacheable actions
    [
        SearchController::class => 'listSearchResults',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

// Clear cache of pages with yellowpages plugins, if a company record was edited/created/deleted in BE
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['yellowpages2_clearcache']
    = ClearCacheHook::class . '->clearCachePostProc';
