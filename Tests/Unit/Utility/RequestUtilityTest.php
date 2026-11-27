<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Utility;

use JWeiland\Yellowpages2\Utility\RequestUtility;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RequestUtilityTest extends UnitTestCase
{
    private const PLUGIN_NAMESPACE = 'tx_yellowpages2_search';

    #[Test]
    public function getMergedRequestArgumentsReadsGivenRequestInsteadOfGlobalVariable(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/', 'GET'))
            ->withQueryParams([self::PLUGIN_NAMESPACE => ['search' => 'from global request']]);
        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withQueryParams([self::PLUGIN_NAMESPACE => ['search' => 'bakery']]);

        self::assertSame(
            ['search' => 'bakery'],
            RequestUtility::getMergedRequestArguments($request, self::PLUGIN_NAMESPACE),
        );

        unset($GLOBALS['TYPO3_REQUEST']);
    }

    #[Test]
    public function getMergedRequestArgumentsLetsPostOverrideQueryArguments(): void
    {
        $request = (new ServerRequest('https://example.com/', 'POST'))
            ->withQueryParams([self::PLUGIN_NAMESPACE => ['search' => 'bakery', 'category' => '1']])
            ->withParsedBody([self::PLUGIN_NAMESPACE => ['search' => 'butcher']]);

        self::assertSame(
            ['search' => 'butcher', 'category' => '1'],
            RequestUtility::getMergedRequestArguments($request, self::PLUGIN_NAMESPACE),
        );
    }

    #[Test]
    public function getMergedRequestArgumentsIgnoresMissingAndInvalidArguments(): void
    {
        $request = (new ServerRequest('https://example.com/', 'POST'))
            ->withQueryParams([self::PLUGIN_NAMESPACE => 'invalid'])
            ->withParsedBody([self::PLUGIN_NAMESPACE => 'invalid']);

        self::assertSame([], RequestUtility::getMergedRequestArguments($request, self::PLUGIN_NAMESPACE));
        self::assertSame([], RequestUtility::getMergedRequestArguments($request, 'other'));
    }
}
