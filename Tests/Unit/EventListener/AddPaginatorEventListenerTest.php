<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\EventListener;

use JWeiland\Yellowpages2\Event\PostProcessFluidVariablesEvent;
use JWeiland\Yellowpages2\EventListener\AddPaginatorEventListener;
use JWeiland\Yellowpages2\Pagination\CompanyPagination;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class AddPaginatorEventListenerTest extends UnitTestCase
{
    private AddPaginatorEventListener $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $extensionServiceMock = $this->createMock(ExtensionService::class);
        $extensionServiceMock
            ->method('getPluginNamespace')
            ->willReturnCallback(
                static fn(string $extensionName, string $pluginName): string => 'tx_' . strtolower($extensionName)
                    . '_' . strtolower($pluginName),
            );

        $this->subject = new AddPaginatorEventListener($extensionServiceMock);
    }

    protected function tearDown(): void
    {
        unset($this->subject, $GLOBALS['TYPO3_REQUEST']);

        parent::tearDown();
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function validControllerActionsDataProvider(): array
    {
        return [
            'Company list' => ['Directory', 'Company', 'list'],
            'Search listSearchResults' => ['Search', 'Search', 'listSearchResults'],
        ];
    }

    #[Test]
    #[DataProvider('validControllerActionsDataProvider')]
    public function invokeAddsPaginator(string $plugin, string $controller, string $action): void
    {
        $GLOBALS['TYPO3_REQUEST'] = new ServerRequest('https://example.com/', 'GET');
        $event = $this->buildEvent($plugin, $controller, $action);

        $this->subject->__invoke($event);

        self::assertArrayHasKey('paginator', $event->getFluidVariables());
        self::assertInstanceOf(CompanyPagination::class, $event->getFluidVariables()['pagination']);
        self::assertSame($action, $event->getFluidVariables()['actionName']);
    }

    #[Test]
    public function invokeKeepsSearchArgumentsInPaginationOfSearchPlugin(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/', 'POST'))
            ->withParsedBody([
                'tx_yellowpages2_search' => [
                    'search' => 'bakery',
                    'category' => '3',
                ],
            ]);
        $event = $this->buildEvent('Search', 'Search', 'listSearchResults');

        $this->subject->__invoke($event);

        $pagination = $event->getFluidVariables()['pagination'];
        self::assertInstanceOf(CompanyPagination::class, $pagination);
        self::assertSame(
            [
                'search' => 'bakery',
                'category' => '3',
                'currentPage' => 1,
            ],
            $pagination->getFirstPageArguments(),
        );
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function invalidControllerActionsDataProvider(): array
    {
        return [
            'Company search (removed in 9.0.0)' => ['Directory', 'Company', 'search'],
            'Management listMyCompanies' => ['Management', 'Management', 'listMyCompanies'],
            'Search show' => ['Search', 'Search', 'show'],
        ];
    }

    #[Test]
    #[DataProvider('invalidControllerActionsDataProvider')]
    public function invokeWithInvalidControllerActionDoesNotAddPaginator(
        string $plugin,
        string $controller,
        string $action,
    ): void {
        $event = $this->buildEvent($plugin, $controller, $action);

        $this->subject->__invoke($event);

        self::assertArrayNotHasKey('paginator', $event->getFluidVariables());
    }

    private function buildEvent(string $plugin, string $controller, string $action): PostProcessFluidVariablesEvent
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock
            ->method('getControllerExtensionName')
            ->willReturn('Yellowpages2');
        $requestMock
            ->method('getPluginName')
            ->willReturn($plugin);
        $requestMock
            ->method('getControllerName')
            ->willReturn($controller);
        $requestMock
            ->method('getControllerActionName')
            ->willReturn($action);

        $queryMock = $this->createMock(QueryInterface::class);
        $queryMock
            ->method('setLimit')
            ->willReturnSelf();
        $queryMock
            ->method('setOffset')
            ->willReturnSelf();
        $queryMock
            ->method('execute')
            ->willReturn($this->createMock(QueryResultInterface::class));

        $queryResultMock = $this->createMock(QueryResultInterface::class);
        $queryResultMock
            ->method('getQuery')
            ->willReturn($queryMock);
        $queryResultMock
            ->method('count')
            ->willReturn(0);

        return new PostProcessFluidVariablesEvent($requestMock, [], ['companies' => $queryResultMock]);
    }
}
