<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiBase\TableConfigurationArray\Helper;

$lang = Helper::localLangHelperFactory('mai_jobs', 'Default/locallang_tca.xlf');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'MaiJobs',
    'List',
    $lang('plugin.list.title'),
    'mai-content',
    'maispace_plugins_lists',
    '',
    'FILE:EXT:mai_jobs/Configuration/FlexForms/JobsPlugin.xml',
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'MaiJobs',
    'Detail',
    $lang('plugin.detail.title'),
    'mai-content',
    'maispace_plugins_lists',
);
