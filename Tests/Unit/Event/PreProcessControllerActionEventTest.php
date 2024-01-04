<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Event;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use JWeiland\Yellowpages2\Event\PreProcessControllerActionEvent;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * Test case.
 */
class PreProcessControllerActionEventTest extends UnitTestCase
{
    protected PreProcessControllerActionEvent $subject;

    /**
     * @var Request|MockObject
     */
    protected MockObject $requestMock;

    /**
     * @var Arguments|MockObject
     */
    protected $argumentsMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestMock = $this->createMock(Request::class);
        $this->requestMock
            ->method('getControllerName')
            ->willReturn('Company');
        $this->requestMock
            ->method('getControllerActionName')
            ->willReturn('list');

        $this->argumentsMock = $this->createMock(Arguments::class);

        $this->subject = new PreProcessControllerActionEvent(
            $this->requestMock,
            $this->argumentsMock,
            [
                'foo' => 'bar',
            ]
        );
    }

    protected function tearDown(): void
    {
        unset($this->subject);

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getRequestReturnsControllerRequest(): void
    {
        self::assertSame(
            $this->requestMock,
            $this->subject->getRequest()
        );
    }

    /**
     * @test
     */
    public function getControllerNameReturnsCompany(): void
    {
        self::assertSame(
            'Company',
            $this->subject->getControllerName()
        );
    }

    /**
     * @test
     */
    public function getActionNameReturnsList(): void
    {
        self::assertSame(
            'list',
            $this->subject->getActionName()
        );
    }

    /**
     * @test
     */
    public function getArgumentsReturnsArguments(): void
    {
        self::assertSame(
            $this->argumentsMock,
            $this->subject->getArguments()
        );
    }

    /**
     * @test
     */
    public function getSettingsReturnsSettings(): void
    {
        self::assertSame(
            ['foo' => 'bar'],
            $this->subject->getSettings()
        );
    }
}
