<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('jw_api')) {
    $GLOBALS['TCA']['sys_category']['columns']['icon'] = array(
        'exclude' => 1,
        'label' => 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:sys_category.icon',
        'config' => array(
            'type' => 'group',
            'internal_type' => 'file',
            'uploadfolder' => 'uploads/tx_yellowpages2',
            'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
            'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
            'show_thumbs' => true,
            'size' => 5,
            'maxitems' => 1,
            'minitems' => 0,
        )
    );

    $GLOBALS['TCA']['sys_category']['types']['1']['showitem'] = str_replace(',description,', ',description,icon,', $GLOBALS['TCA']['sys_category']['types']['1']['showitem']);
}
