<?php

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Industry Directory',
    'description' => 'Industry directory with frontend self-registration, moderation via signed mail links, EXT:maps2 integration and native FAL uploads for logo and images',
    'category' => 'plugin',
    'author' => 'Stefan Froemken, Hoja Mustaffa Abdul Latheef',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '9.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'glossary2' => '7.0.0-0.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'maps2' => '',
            'checkfaluploads' => '6.0.0-6.99.99',
        ],
    ],
];
