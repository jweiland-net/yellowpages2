<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'JWeiland.' . $_EXTKEY,
	'Directory',
	array(
		'Company' => 'list, listMyCompanies, show, search, new, create, edit, update, activate',
		'Map' => 'new, create, edit, update, activate',
		'Email' => 'send',
	),
	// non-cacheable actions
	array(
		'Company' => 'search, create, update',
		'Map' => 'create, update',
	)
);

// use hook to automatically add a map record to current yellow page
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'JWeiland\\Yellowpages2\\Tca\\CreateMap';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['JWeiland\\Yellowpages2\\Tasks\\Update'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'Update yellowpages',
	'description'      => 'Hide all yellowpages records which are older than the secified age.'
);