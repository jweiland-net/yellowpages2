<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Industry Directory',
    'description' => 'With this extension you can build your own industry directory',
    'category' => 'plugin',
    'author' => 'Stefan Froemken, Hoja Mustaffa Abdul Latheef',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '7.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.33-12.4.99',
            'glossary2' => '6.0.0-6.99.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'checkfaluploads' => '',
            'maps2' => '9.3.0-0.0.0',
        ],
    ],
];
