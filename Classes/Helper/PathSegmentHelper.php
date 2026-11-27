<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Helper;

use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * Helper class to generate a path segment (slug) for a company record.
 * Used while executing the UpgradeWizard and saving records in the frontend.
 */
final readonly class PathSegmentHelper
{
    private const TABLE = 'tx_yellowpages2_domain_model_company';

    /**
     * The record needs the columns "uid" and "company"
     *
     * @param array<string, int|string|null> $baseRecord
     */
    public function generatePathSegment(array $baseRecord, int $pid): string
    {
        return $this->getSlugHelper()->generate($baseRecord, $pid);
    }

    private function getSlugHelper(): SlugHelper
    {
        // Add uid to slug, to prevent duplicates
        $config = (array)($GLOBALS['TCA'][self::TABLE]['columns']['path_segment']['config'] ?? []);
        $config['generatorOptions']['fields'] = ['company', 'uid'];

        return GeneralUtility::makeInstance(
            SlugHelper::class,
            self::TABLE,
            'path_segment',
            $config,
        );
    }
}
