<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Pagination;

use JWeiland\Yellowpages2\Pagination\CompanyPagination;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class CompanyPaginationTest extends UnitTestCase
{
    #[Test]
    public function constructorKeepsArgumentsOfGivenRequestOnly(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/', 'GET'))
            ->withQueryParams(['tx_yellowpages2_search' => ['search' => 'from global request']]);
        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withQueryParams([
                'tx_yellowpages2_search' => [
                    'search' => 'bakery',
                    '__trustedProperties' => 'secret',
                    'controller' => 'Search',
                    'currentPage' => '2',
                ],
            ]);

        $subject = new CompanyPagination(new ArrayPaginator(range(1, 40), 1, 10), 'tx_yellowpages2_search', $request);

        self::assertSame(['search' => 'bakery', 'currentPage' => 1], $subject->getFirstPageArguments());

        unset($GLOBALS['TYPO3_REQUEST']);
    }

    #[Test]
    public function constructorWithoutRequestKeepsNoArguments(): void
    {
        $subject = new CompanyPagination(new ArrayPaginator(range(1, 40), 1, 10));

        self::assertSame(['currentPage' => 1], $subject->getFirstPageArguments());
    }
}
