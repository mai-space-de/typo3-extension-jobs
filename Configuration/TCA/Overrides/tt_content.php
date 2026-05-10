<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiBase\TableConfigurationArray\CType;
use Maispace\MaiBase\TableConfigurationArray\Helper;

$lang = Helper::localLangHelperFactory('mai_jobs', 'Default/locallang_tca.xlf');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'MaiJobs',
    'List',
    $lang('plugin.list.title'),
    'ext-maispace-mai_jobs',
    'maispace_feature',
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'MaiJobs',
    'Detail',
    $lang('plugin.detail.title'),
    'ext-maispace-mai_jobs',
    'maispace_feature',
);

(new CType('maispace_jobs_list', $lang('ctype.jobs_list'), 'ext-maispace-mai_jobs'))
    ->addDefaultHeaderPalette()
    ->addCustomFields('pi_flexform')
    ->addDefaultLanguageTab()
    ->addDefaultAccessTab()
    ->setGroup('maispace_feature')
    ->register();

(new CType('maispace_jobs_detail', $lang('ctype.jobs_detail'), 'ext-maispace-mai_jobs'))
    ->addDefaultHeaderPalette()
    ->addDefaultLanguageTab()
    ->addDefaultAccessTab()
    ->setGroup('maispace_feature')
    ->register();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:mai_jobs/Configuration/FlexForms/JobsPlugin.xml',
    'maispace_jobs_list',
);
