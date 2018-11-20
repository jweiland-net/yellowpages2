<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Add tx_maps2_uid column to company table
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('maps2')) {
    \JWeiland\Maps2\Tca\Maps2Registry::getInstance()->add(
        'yellowpages2',
        'tx_yellowpages2_domain_model_company',
        [
            'addressColumns' => ['street', 'house_number', 'zip', 'city'],
            'defaultCountry' => 'Deutschland',
            'defaultStoragePid' => [
                'extKey' => 'yellowpages2',
                'property' => 'poiCollectionPid'
            ],
            'synchronizeColumns' => [
                [
                    'foreignColumnName' => 'company',
                    'poiCollectionColumnName' => 'title'
                ]
            ]
        ]
    );
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    'yellowpages2',
    'tx_yellowpages2_domain_model_company',
    'main_trade',
    [
        'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.mainTrade',
        'fieldConfiguration' => [
            'minitems' => 1,
            'maxitems' => 1
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
