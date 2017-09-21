<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    'yellowpages2',
    'tx_yellowpages2_domain_model_company',
    'main_trade',
    [
        'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.mainTrade',
        'fieldConfiguration' => [
            'minitems' => 0,
            'maxitems' => 1,
            'MM' => null,
            'MM_opposite_field' => null,
            'MM_match_fields' => null
        ]
    ]
);

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
