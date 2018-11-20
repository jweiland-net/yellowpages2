<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JWeiland.yellowpages2',
    'Directory',
    'LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:plugin.title'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'yellowpages2',
    'Configuration/TypoScript',
    'Industry Directory'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['yellowpages2_directory'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['yellowpages2_directory'] = 'select_key';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'yellowpages2_directory',
    'FILE:EXT:yellowpages2/Configuration/FlexForms/YellowPages.xml'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_yellowpages2_domain_model_company',
    'EXT:yellowpages2/Resources/Private/Language/locallang_csh_tx_yellowpages2_domain_model_company.xlf'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_yellowpages2_domain_model_company');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_yellowpages2_domain_model_district',
    'EXT:yellowpages2/Resources/Private/Language/locallang_csh_tx_yellowpages2_domain_model_district.xlf'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_yellowpages2_domain_model_district');
