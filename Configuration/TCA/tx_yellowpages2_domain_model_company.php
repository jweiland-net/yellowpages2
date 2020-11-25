<?php
return [
    'ctrl' => [
        'title'    => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company',
        'label' => 'company',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY company',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime'
        ],
        'searchFields' => 'company,logo,street,house_number,zip,city,telephone,fax,contact_person,email,website,opening_times,barrier_free,description,district,fe_user,',
        'iconfile' => 'EXT:yellowpages2/Resources/Public/Icons/tx_yellowpages2_domain_model_company.svg'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, company, path_segment, logo, images, street, house_number, zip, city, telephone, fax, contact_person, email, website, opening_times, barrier_free, description, district, fe_user, facebook, twitter, google, main_trade, trades'
    ],
    'types' => [
        '1' => [
            'showitem' => '--palette--;;languageHidden, company, path_segment,
            logo, images, 
            --palette--;;streetHouseNumber, --palette--;;zipCity, --palette--;;districtBarrierFree,
            --palette--;;telephoneFax, --palette--;;emailWebsite, contact_person,
            opening_times, description, fe_user,
            --div--;LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tabs.social, facebook, twitter, google,
            --div--;LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tabs.trades, main_trade, trades,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access, 
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access'
        ]
    ],
    'palettes' => [
        'languageHidden' => ['showitem' => 'sys_language_uid, l10n_parent, hidden'],
        'streetHouseNumber' => ['showitem' => 'street, house_number'],
        'districtBarrierFree' => ['showitem' => 'district, barrier_free'],
        'zipCity' => ['showitem' => 'zip, city'],
        'telephoneFax' => ['showitem' => 'telephone, fax'],
        'emailWebsite' => ['showitem' => 'email, website'],
        'access' => [
            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
        ]
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ],
                ],
                'default' => 0,
            ]
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_yellowpages2_domain_model_company',
                'foreign_table_where' => 'AND tx_yellowpages2_domain_model_company.pid=###CURRENT_PID### AND tx_yellowpages2_domain_model_company.sys_language_uid IN (-1,0)',
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => true,
                    ],
                ],
                'default' => 0,
            ]
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => ''
            ]
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0
            ]
        ],
        'cruser_id' => [
            'label' => 'cruser_id',
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'pid' => [
            'label' => 'pid',
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 16,
                'eval' => 'datetime,int',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ]
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 16,
                'eval' => 'datetime,int',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ]
        ],
        'company' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.company',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ]
        ],
        'path_segment' => [
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.path_segment',
            'displayCond' => 'VERSION:IS:false',
            'config' => [
                'type' => 'slug',
                'size' => 50,
                'generatorOptions' => [
                    'fields' => ['company'],
                    // Do not add pageSlug, as we add pageSlug on our own in RouteEnhancer
                    'prefixParentPageSlug' => false,
                    'fieldSeparator' => '-',
                    'replacements' => [
                        '/' => '-'
                    ],
                ],
                'fallbackCharacter' => '-',
                'eval' => 'uniqueInSite',
                'default' => ''
            ]
        ],
        'logo' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.logo',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'logo',
                [
                    'minitems' => 0,
                    'maxitems' => 1,
                    'foreign_match_fields' => [
                        'fieldname' => 'logo',
                        'tablenames' => 'tx_yellowpages2_domain_model_company',
                        'table_local' => 'sys_file',
                    ],
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
                    ],
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.logo.add',
                        'showPossibleLocalizationRecords' => true,
                        'showRemovedLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.audioOverlayPalette;audioOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.videoOverlayPalette;videoOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ]
                        ],
                    ],
                ],
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext']
            )
        ],
        'images' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.images',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'images',
                [
                    'minitems' => 0,
                    'maxitems' => 5,
                    'foreign_match_fields' => [
                        'fieldname' => 'images',
                        'tablenames' => 'tx_yellowpages2_domain_model_company',
                        'table_local' => 'sys_file',
                    ],
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
                    ],
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.images.add',
                        'showPossibleLocalizationRecords' => true,
                        'showRemovedLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.audioOverlayPalette;audioOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.videoOverlayPalette;videoOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ]
                        ],
                    ],
                ],
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext']
            )
        ],
        'street' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.street',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'house_number' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.houseNumber',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'zip' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.zip',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'city' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'telephone' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.telephone',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'fax' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.fax',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'contact_person' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.contactPerson',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'email' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'website' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.website',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'opening_times' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.openingTimes',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'eval' => 'trim'
            ]
        ],
        'barrier_free' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.barrierFree',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0
            ]
        ],
        'description' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.description',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
                'eval' => 'required,trim'
            ],
        ],
        'district' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.district',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_yellowpages2_domain_model_district',
                'foreign_table_where' => 'ORDER BY tx_yellowpages2_domain_model_district.district',
                'items' => [
                    ['LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.district.pleaseChoose', 0]
                ],
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0
            ]
        ],
        'fe_user' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.feUser',
            'description' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.feUser.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'ORDER BY fe_users.username',
                'items' => [
                    ['LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.feUser.pleaseChoose', 0]
                ],
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0
            ]
        ],
        'facebook' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.facebook',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputLink',
                'size' => 30,
                'eval' => 'trim',
                'softref' => 'typolink[linkList]'
            ]
        ],
        'twitter' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.twitter',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputLink',
                'size' => 30,
                'eval' => 'trim',
                'softref' => 'typolink[linkList]'
            ]
        ],
        'google' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:tx_yellowpages2_domain_model_company.google',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputLink',
                'size' => 30,
                'eval' => 'trim',
                'softref' => 'typolink[linkList]'
            ]
        ]
    ]
];
