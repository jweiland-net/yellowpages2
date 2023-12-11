<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'yellowpages2',
        'Directory',
        [
            \JWeiland\Yellowpages2\Controller\CompanyController::class => 'list, listMyCompanies, show, search, new, create, edit, update, activate',
            \JWeiland\Yellowpages2\Controller\MapController::class => 'new, create, edit, update, activate',
        ],
        // non-cacheable actions
        [
            \JWeiland\Yellowpages2\Controller\CompanyController::class => 'search, create, update, activate',
            \JWeiland\Yellowpages2\Controller\MapController::class => 'create, update',
        ]
    );

    // add yellowpages2 plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:yellowpages2/Configuration/TSconfig/ContentElementWizard.txt">'
    );

    // Clear cache of pages with yellowpages plugins, if a company record was edited/created/deleted in BE
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['yellowpages2_clearcache']
        = \JWeiland\Yellowpages2\Hook\ClearCacheHook::class . '->clearCachePostProc';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['yellowpages2UpdateSlug']
        = \JWeiland\Yellowpages2\Updater\Yellowpages2SlugUpdater::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][JWeiland\Yellowpages2\Tasks\Update::class] = [
        'extension' => 'yellowpages2',
        'title' => 'Inform users to update their company record',
        'description' => 'Hide company records which are older than 13 month and inform users to update their company record  after 12 month.',
    ];
});
