<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Backend User Administration',
    'description' => 'Backend user administration and overview. Allows you to compare the settings of users and verify their permissions and see who is online.',
    'category' => 'module',
    'author' => 'TYPO3 Core Team',
    'author_email' => 'typo3cms@typo3.org',
    'author_company' => '',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '10.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.2.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
