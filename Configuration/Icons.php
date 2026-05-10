<?php

declare(strict_types=1);

return [
    'ext-maispace-mai_jobs' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_jobs/Resources/Public/Icons/Extension.svg',
    ],
    'tx-maijobs-job' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_jobs/Resources/Public/Icons/tx_maijobs_job.svg',
    ],
    'tx-maijobs-application' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_jobs/Resources/Public/Icons/tx_maijobs_application.svg',
    ],
];
