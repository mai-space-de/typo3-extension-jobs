<?php

declare(strict_types=1);

return [
    \Maispace\MaiJobs\Domain\Model\Job::class => [
        'tableName' => 'tx_maijobs_job',
    ],
    \Maispace\MaiJobs\Domain\Model\Application::class => [
        'tableName' => 'tx_maijobs_application',
    ],
];
