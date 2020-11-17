<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Industry Directory',
    'description' => 'With this extension you can build your own industry directory',
    'category' => 'plugin',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '4.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
            'maps2' => '8.0.0-0.0.0',
            'glossary2' => '4.0.0-4.99.99'
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'checkfaluploads' => ''
        ]
    ],
];
