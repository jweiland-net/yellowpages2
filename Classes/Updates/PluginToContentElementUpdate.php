<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

/**
 * With TYPO3 13 plugins have to be declared as content elements (CType) instead of "list_type"
 */
#[UpgradeWizard('yellowpages2_migratePluginsToContentElementsUpdate')]
class PluginToContentElementUpdate extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'yellowpages2_directory' => 'yellowpages2_directory',
        ];
    }

    public function getTitle(): string
    {
        return '[yellowpages2] Migrate plugins to Content Elements';
    }

    public function getDescription(): string
    {
        return 'The modern way to register a plugin in TYPO3 is as a content element type (CType) instead '
            . 'of the legacy "list_type". Running this wizard migrates the yellowpages2 directory plugin accordingly.';
    }
}
