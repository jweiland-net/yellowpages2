<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {
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
            \JWeiland\Yellowpages2\Controller\MapController::class => 'create, update'
        ]
    );

    // Register SVG Icon Identifier
    $svgIcons = [
        'ext-yellowpages2-directory-wizard-icon' => 'plugin_wizard.svg',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    foreach ($svgIcons as $identifier => $fileName) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:yellowpages2/Resources/Public/Icons/' . $fileName]
        );
    }

    // add yellowpages2 plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:yellowpages2/Configuration/TSconfig/ContentElementWizard.txt">'
    );

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['yellowpages2UpdateSlug']
        = \JWeiland\Yellowpages2\Updater\Yellowpages2SlugUpdater::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][JWeiland\Yellowpages2\Tasks\Update::class] = [
        'extension'        => 'yellowpages2',
        'title'            => 'Update yellowpages',
        'description'      => 'Hide all yellowpages records which are older than the secified age.'
    ];
});
