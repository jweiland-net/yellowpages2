<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}

$finder = PhpCsFixer\Finder::create()
    ->name('*.php')
    ->in(__DIR__ . '/../..')
    // Configuration files do not need header comments
    ->exclude('Configuration')
    ->notName('ext_localconf.php')
    ->notName('ext_tables.php')
    ->notName('ext_emconf.php')
    // ClassAliasMap files do not need header comments
    ->notName('ClassAliasMap.php')
    // CodeSnippets and Examples in Documentation do not need header comments
    ->exclude('Documentation');

$headerComment = <<<COMMENT
This file is part of the package jweiland/yellowpages2.

For the full copyright and license information, please read the
LICENSE file that was distributed with this source code.
COMMENT;

return (new \PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        'no_extra_blank_lines' => true,
        'header_comment' => [
            'header' => $headerComment,
            'comment_type' => 'comment',
            'separate' => 'both',
            'location' => 'after_declare_strict',
        ],
    ])
    ->setFinder($finder);
