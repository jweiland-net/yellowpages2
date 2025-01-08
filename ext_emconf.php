<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Industry Directory',
    'description' => 'With this extension you can build your own industry directory',
    'category' => 'plugin',
    'author' => 'Stefan Froemken, Hoja Mustaffa Abdul Latheef',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '8.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'glossary2' => '7.0.0-0.0.0',
        ],
        'conflicts' => [
        ],
    ],
];
