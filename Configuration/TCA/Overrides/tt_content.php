<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiBase\TableConfigurationArray\Helper;

$lang = Helper::localLangHelperFactory('mai_jobs', 'Default/locallang_tca.xlf');

// Plugin registration (list_type + PLUGIN_TYPE_CONTENT_ELEMENT CTypes
// maijobs_list / maijobs_detail) lives in ext_localconf.php via
// ExtensionUtility::configurePlugin(). The maispace_jobs_list/_detail
// CTypes previously registered here were an orphaned legacy duplicate
// with no renderer attached and have been removed.

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:mai_jobs/Configuration/FlexForms/JobsPlugin.xml',
    'maijobs_list',
);
