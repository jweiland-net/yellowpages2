<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Industry Directory',
    'description' => 'With this extension you can build your own industry directory',
    'category' => 'plugin',
    'author' => 'Stefan Froemken, Hoja Mustaffa Abdul Latheef',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '7.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.8-12.4.99',
            'glossary2' => '6.0.0-0.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'maps2' => '11.0.0-0.0.0',
        ],
    ],
];
