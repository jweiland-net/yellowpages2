<?php

if (!defined('TYPO3')) {
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
                'property' => 'poiCollectionPid',
            ],
            'synchronizeColumns' => [
                [
                    'foreignColumnName' => 'company',
                    'poiCollectionColumnName' => 'title',
                ],
            ],
        ],
    );
}
