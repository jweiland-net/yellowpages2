<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

// Add tx_maps2_uid column to company table. Since maps2 13 the relation is declared
// with the TCA renderType "maps2Relation", which maps2 completes and registers itself.
if (ExtensionManagementUtility::isLoaded('maps2')) {
    ExtensionManagementUtility::addTCAcolumns(
        'tx_yellowpages2_domain_model_company',
        [
            'tx_maps2_uid' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => ['street', 'house_number', 'zip', 'city'],
                    'defaultCountry' => 'Deutschland',
                    'defaultStoragePid' => [
                        'type' => 'extensionmanager',
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
            ],
        ],
    );

    ExtensionManagementUtility::addToAllTCAtypes(
        'tx_yellowpages2_domain_model_company',
        '--div--;maps2.db:tab.maps2.gm, tx_maps2_uid',
    );
}
