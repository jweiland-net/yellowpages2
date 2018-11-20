<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JWeiland.yellowpages2',
    'Directory',
    [
        'Company' => 'list, listMyCompanies, show, search, new, create, edit, update, activate',
        'Map' => 'new, create, edit, update, activate',
        'Email' => 'send'
    ],
    // non-cacheable actions
    [
        'Company' => 'search, create, update',
        'Map' => 'create, update'
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][JWeiland\Yellowpages2\Tasks\Update::class] = [
    'extension'        => 'yellowpages2',
    'title'            => 'Update yellowpages',
    'description'      => 'Hide all yellowpages records which are older than the secified age.'
];
