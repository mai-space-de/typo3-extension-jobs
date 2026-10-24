<?php

declare(strict_types=1);

$allIcons = 'EXT:mai_base/Resources/Public/Icons/all-icons/';

return [
    'ext-maispace-mai_jobs' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_jobs/Resources/Public/Icons/Extension.svg',
    ],
    'mai-jobs' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'shopping-bag-suitcase-1--product-business-briefcase--Streamline-Core.svg',
    ],
    'mai-jobs-application' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-file-clipboard-check--checkmark-edit-task-edition-checklist-check-success-clipboard-form--Streamline-Core.svg',
    ],
    'tx-maijobs-job' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'shopping-bag-suitcase-1--product-business-briefcase--Streamline-Core.svg',
    ],
    'tx-maijobs-application' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-file-clipboard-check--checkmark-edit-task-edition-checklist-check-success-clipboard-form--Streamline-Core.svg',
    ],
];
