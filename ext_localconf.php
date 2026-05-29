<?php

declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'MaiJobs',
    'List',
    [
        \Maispace\MaiJobs\Controller\JobController::class => 'list',
    ],
    [],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'MaiJobs',
    'Detail',
    [
        \Maispace\MaiJobs\Controller\JobController::class => 'detail',
        \Maispace\MaiJobs\Controller\ApplicationController::class => 'apply,confirm',
    ],
    [
        \Maispace\MaiJobs\Controller\ApplicationController::class => 'apply,confirm',
    ],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['mai_jobs']
    = \Maispace\MaiJobs\Hook\JobCacheInvalidationHook::class;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['mai_jobs']
    = \Maispace\MaiJobs\Hook\JobCacheInvalidationHook::class;
