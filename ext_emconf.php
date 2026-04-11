<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Mai Jobs',
    'description' => 'Job listing records with title, description, requirements, location, and application deadline. Categories use TYPO3 sys_category.',
    'category' => 'module',
    'author' => 'Maispace',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
