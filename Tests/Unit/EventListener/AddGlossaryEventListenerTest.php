<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\EventListener;

use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Event\PostProcessFluidVariablesEvent;
use JWeiland\Yellowpages2\EventListener\AddGlossaryEventListener;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
class AddGlossaryEventListenerTest extends UnitTestCase
{
    protected AddGlossaryEventListener $subject;

    /**
     * @var QueryResultInterface|MockObject
     */
    protected $queryResultMock;

    /**
     * @var GlossaryService|MockObject
     */
    protected $glossaryServiceMock;

    /**
     * @var CompanyRepository|MockObject
     */
    protected $companyRepositoryMock;

    protected PostProcessFluidVariablesEvent $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queryResultMock = $this->createMock(QueryResult::class);

        $this->glossaryServiceMock = $this->createMock(GlossaryService::class);
        $this->glossaryServiceMock
            ->method('buildGlossary')
            ->willReturn('html');

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

        $this->event = new PostProcessFluidVariablesEvent($requestMock, [], []);

        $this->subject = new AddGlossaryEventListener(
            $this->glossaryServiceMock,
            $this->companyRepositoryMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->glossaryServiceMock,
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
}
