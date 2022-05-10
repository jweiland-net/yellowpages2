<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Industry Directory',
    'description' => 'With this extension you can build your own industry directory',
    'category' => 'plugin',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '5.1.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.14-10.4.99',
            'glossary2' => '5.0.0-0.0.0'
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'checkfaluploads' => '',
            'maps2' => '9.3.0-0.0.0'
        ]
    ],
];
