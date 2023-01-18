<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Event;

use JWeiland\Yellowpages2\Event\PostProcessFluidVariablesEvent;
use JWeiland\Yellowpages2\Event\PreProcessControllerActionEvent;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * Test case.
 */
class PreProcessControllerActionEventTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var PostProcessFluidVariablesEvent
     */
    protected $subject;

    /**
     * @var Request|ObjectProphecy
     */
    protected $requestProphecy;

    /**
     * @var Arguments|ObjectProphecy
     */
    protected $argumentsProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestProphecy = $this->prophesize(Request::class);
        $this->requestProphecy
            ->getControllerName()
            ->willReturn('Company');
        $this->requestProphecy
            ->getControllerActionName()
            ->willReturn('list');

        $this->argumentsProphecy = $this->prophesize(Arguments::class);

        $this->subject = new PreProcessControllerActionEvent(
            $this->requestProphecy->reveal(),
            $this->argumentsProphecy->reveal(),
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
            $this->requestProphecy->reveal(),
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
            $this->argumentsProphecy->reveal(),
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
