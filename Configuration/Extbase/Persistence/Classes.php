<?php

declare(strict_types=1);

return [
    \Maispace\MaiJobs\Domain\Model\Job::class => [
        'tableName' => 'pages',
        'properties' => [
            'title' => [
                'fieldName' => 'title',
            ],
            'slug' => [
                'fieldName' => 'slug',
            ],
            'description' => [
                'fieldName' => 'tx_maijobs_description',
            ],
            'requirements' => [
                'fieldName' => 'tx_maijobs_requirements',
            ],
            'deadline' => [
                'fieldName' => 'tx_maijobs_deadline',
            ],
            'status' => [
                'fieldName' => 'tx_maijobs_status',
            ],
            'doktype' => [
                'fieldName' => 'doktype',
            ],
            // categories uses the core pages.categories MM (tablenames=pages).
        ],
    ],
    \Maispace\MaiJobs\Domain\Model\Application::class => [
        'tableName' => 'tx_maijobs_application',
    ],
];
