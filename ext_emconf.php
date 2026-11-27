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
    'version' => '11.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.3.99',
            'glossary2' => '8.0.0-8.99.99',
            'form' => '14.3.0-14.3.99',
            'maps2' => '13.0.0-13.99.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'checkfaluploads' => '6.0.0-6.99.99',
            'rte_ckeditor' => '14.3.0-14.3.99',
        ],
    ],
];
