<?php

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Yellowpages2\Controller\CompanyController;
use JWeiland\Yellowpages2\Controller\MapController;
use JWeiland\Yellowpages2\Hook\ClearCacheHook;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function () {
    ExtensionUtility::configurePlugin(
        'yellowpages2',
        'Directory',
        [
            CompanyController::class => 'list, listMyCompanies, show, search, new, create, edit, update, activate',
            MapController::class => 'new, create, edit, update, activate',
        ],
        // non-cacheable actions
        [
            CompanyController::class => 'search, create, update, activate',
            MapController::class => 'create, update',
        ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
    );

    // Clear cache of pages with yellowpages plugins, if a company record was edited/created/deleted in BE
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['yellowpages2_clearcache']
        = ClearCacheHook::class . '->clearCachePostProc';
});
