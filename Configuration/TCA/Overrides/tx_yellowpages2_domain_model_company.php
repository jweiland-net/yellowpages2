<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    'yellowpages2',
    'tx_yellowpages2_domain_model_company',
    'trades',
    [
        'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.trades',
        'fieldConfiguration' => [
            'minitems' => 0,
            'maxitems' => 2
        ]
    ]
);
