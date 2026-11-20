<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Configuration;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Site\Set\SetDefinition;
use TYPO3\CMS\Core\Site\Set\SetRegistry;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class SiteSetTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/maps2',
        'jweiland/yellowpages2',
    ];

    #[Test]
    public function siteSetIsValid(): void
    {
        $setRegistry = $this->get(SetRegistry::class);

        self::assertTrue($setRegistry->hasSet('jweiland/yellowpages2'));
        self::assertArrayNotHasKey('jweiland/yellowpages2', $setRegistry->getInvalidSets());
    }

    /**
     * plugin.tx_form, which configures EXT:form in frontend, is part of the set "typo3/form" only
     */
    #[Test]
    public function siteSetLoadsFormFrameworkAndMaps2(): void
    {
        $setNames = array_map(
            static fn(SetDefinition $set): string => $set->name,
            $this->get(SetRegistry::class)->getSets('jweiland/yellowpages2'),
        );

        self::assertContains('typo3/form', $setNames);
        self::assertContains('jweiland/maps2-default', $setNames);
    }
}
