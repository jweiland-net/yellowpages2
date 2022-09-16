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
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * Test case.
 */
class PostProcessFluidVariablesEventTest extends UnitTestCase
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

        $this->subject = new PostProcessFluidVariablesEvent(
            $this->requestProphecy->reveal(),
            [
                'foo' => 'bar'
            ],
            [
                'name' => 'Stefan Froemken'
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
    public function getSettingsReturnsSettings(): void
    {
        self::assertSame(
            ['foo' => 'bar'],
            $this->subject->getSettings()
        );
    }

    /**
     * @test
     */
    public function getFluidVariablesReturnsVariables(): void
    {
        self::assertSame(
            ['name' => 'Stefan Froemken'],
            $this->subject->getFluidVariables()
        );
    }

    /**
     * @test
     */
    public function addFluidVariablesWillAddFluidVariable(): void
    {
        $this->subject->addFluidVariable('company', 'jweiland.net');
        self::assertSame(
            [
                'name' => 'Stefan Froemken',
                'company' => 'jweiland.net'
            ],
            $this->subject->getFluidVariables()
        );
    }
}
