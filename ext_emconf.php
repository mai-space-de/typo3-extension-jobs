<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Mai Jobs',
    'description' => 'Jobs extension with job listings, category filtering, status management, and application form with file upload.',
    'category' => 'module',
    'author' => 'Maispace',
    'author_email' => '',
    'state' => 'alpha',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'mai_mail' => '',
        ],
    ],
];
