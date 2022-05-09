<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Event;

use JWeiland\Yellowpages2\Controller\CompanyController;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Event\PostProcessControllerActionEvent;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * Test case.
 */
class PostProcessControllerActionEventTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var PostProcessControllerActionEvent
     */
    protected $subject;

    /**
     * @var Company|ObjectProphecy
     */
    protected $companyProphecy;

    /**
     * @var Request|ObjectProphecy
     */
    protected $requestProphecy;

    /**
     * @var ControllerContext|ObjectProphecy
     */
    protected $controllerContextProphecy;

    /**
     * @var CompanyController|ObjectProphecy
     */
    protected $companyControllerProphecy;

    public function setUp(): void
    {
        parent::setUp();

        $this->companyProphecy = $this->prophesize(Company::class);

        $this->requestProphecy = $this->prophesize(Request::class);
        $this->requestProphecy
            ->getControllerName()
            ->willReturn('Company');
        $this->requestProphecy
            ->getControllerActionName()
            ->willReturn('list');

        $this->controllerContextProphecy = $this->prophesize(ControllerContext::class);
        $this->controllerContextProphecy
            ->getRequest()
            ->willReturn($this->requestProphecy->reveal());

        $this->companyControllerProphecy = $this->prophesize(CompanyController::class);
        $this->companyControllerProphecy
            ->getControllerContext()
            ->willReturn($this->controllerContextProphecy->reveal());

        $this->subject = new PostProcessControllerActionEvent(
            $this->companyControllerProphecy->reveal(),
            $this->companyProphecy->reveal(),
            [
                'foo' => 'bar'
            ]
        );
    }

    public function tearDown(): void
    {
        unset($this->subject);

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getControllerReturnsActionController(): void
    {
        self::assertSame(
            $this->companyControllerProphecy->reveal(),
            $this->subject->getController()
        );
    }

    /**
     * @test
     */
    public function getCompanyControllerReturnsCompanyController(): void
    {
        self::assertSame(
            $this->companyControllerProphecy->reveal(),
            $this->subject->getCompanyController()
        );
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
    public function getCompanyReturnsCompany(): void
    {
        self::assertSame(
            $this->companyProphecy->reveal(),
            $this->subject->getCompany()
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
