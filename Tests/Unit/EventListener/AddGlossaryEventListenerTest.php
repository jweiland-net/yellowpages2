<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\EventListener;

use JWeiland\Glossary2\Configuration\ExtConf;
use JWeiland\Glossary2\Helper\CharsetHelper;
use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Event\PostProcessFluidVariablesEvent;
use JWeiland\Yellowpages2\EventListener\AddGlossaryEventListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
final class AddGlossaryEventListenerTest extends UnitTestCase
{
    protected AddGlossaryEventListener $subject;

    protected QueryResult&MockObject $queryResultMock;

    protected GlossaryService $glossaryService;

    protected CompanyRepository&MockObject $companyRepositoryMock;

    protected PostProcessFluidVariablesEvent $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queryResultMock = $this->createMock(QueryResult::class);

        // GlossaryService is final since glossary2 8.0.0 and can not be doubled.
        // Use a real instance and mock its collaborators, so the view renders "html".
        $viewMock = $this->createMock(ViewInterface::class);
        $viewMock
            ->method('render')
            ->willReturn('html');

        $viewFactoryMock = $this->createMock(ViewFactoryInterface::class);
        $viewFactoryMock
            ->method('create')
            ->willReturn($viewMock);

        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $eventDispatcherMock
            ->method('dispatch')
            ->willReturnArgument(0);

        $this->glossaryService = new GlossaryService(
            new ExtConf($this->createMock(ExtensionConfiguration::class)),
            $eventDispatcherMock,
            $viewFactoryMock,
            new CharsetHelper($this->createMock(CharsetConverter::class), $eventDispatcherMock),
            new TypoScriptService(),
            new NullLogger(),
        );

        $this->companyRepositoryMock = $this->createMock(CompanyRepository::class);
        $this->companyRepositoryMock
            ->method('getExtbaseQueryToFindAllEntries')
            ->willReturn($this->queryResultMock);

        $requestMock = $this->createMock(Request::class);
        $requestMock
            ->method('getControllerName')
            ->willReturn('Company');
        $requestMock
            ->method('getControllerActionName')
            ->willReturn('list');
        $requestMock
            ->method('getAttribute')
            ->willReturnCallback(
                // GlossaryService reads the site and the frontend TypoScript of the request
                static fn(string $name): ?FrontendTypoScript => $name === 'frontend.typoscript'
                    ? new FrontendTypoScript(new RootNode(), [], [], [])
                    : null,
            );

        $this->event = new PostProcessFluidVariablesEvent($requestMock, [], []);

        $this->subject = new AddGlossaryEventListener(
            $this->glossaryService,
            $this->companyRepositoryMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->glossaryService,
            $this->companyRepositoryMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function invokeToAddGlossary(): void
    {
        $this->subject->__invoke($this->event);

        self::assertSame(
            'html',
            $this->event->getFluidVariables()['glossar'],
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidControllerActionsDataProvider(): array
    {
        return [
            'Company search (removed in 9.0.0)' => ['Company', 'search'],
            'Company show' => ['Company', 'show'],
            'Search listSearchResults' => ['Search', 'listSearchResults'],
            'Management listMyCompanies' => ['Management', 'listMyCompanies'],
        ];
    }

    #[Test]
    #[DataProvider('invalidControllerActionsDataProvider')]
    public function invokeWithInvalidControllerActionDoesNotAddGlossary(string $controller, string $action): void
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock
            ->method('getControllerName')
            ->willReturn($controller);
        $requestMock
            ->method('getControllerActionName')
            ->willReturn($action);
        $event = new PostProcessFluidVariablesEvent($requestMock, [], []);

        $this->subject->__invoke($event);

        self::assertArrayNotHasKey('glossar', $event->getFluidVariables());
    }
}
