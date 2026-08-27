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
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
class PostProcessControllerActionEventTest extends UnitTestCase
{
    protected PostProcessControllerActionEvent $subject;

    protected Company $companyMock;

    protected Request $requestMock;

    protected CompanyController $companyControllerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyMock = $this->createMock(Company::class);
        $this->companyControllerMock = $this->createMock(CompanyController::class);

        $this->requestMock = $this->createMock(Request::class);
        $this->requestMock
            ->method('getControllerName')
            ->willReturn('Company');
        $this->requestMock
            ->method('getControllerActionName')
            ->willReturn('list');

        $this->subject = new PostProcessControllerActionEvent(
            $this->companyControllerMock,
            $this->companyMock,
            [
                'foo' => 'bar',
            ],
            $this->requestMock,
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
    public function getControllerReturnsActionController(): void
    {
        self::assertSame(
            $this->companyControllerMock,
            $this->subject->getController(),
        );
    }

    /**
     * @test
     */
    public function getCompanyControllerReturnsCompanyController(): void
    {
        self::assertSame(
            $this->companyControllerMock,
            $this->subject->getCompanyController(),
        );
    }

    /**
     * @test
     */
    public function getRequestReturnsControllerRequest(): void
    {
        self::assertSame(
            $this->requestMock,
            $this->subject->getRequest(),
        );
    }

    /**
     * @test
     */
    public function getControllerNameReturnsCompany(): void
    {
        self::assertSame(
            'Company',
            $this->subject->getControllerName(),
        );
    }

    /**
     * @test
     */
    public function getActionNameReturnsList(): void
    {
        self::assertSame(
            'list',
            $this->subject->getActionName(),
        );
    }

    /**
     * @test
     */
    public function getCompanyReturnsCompany(): void
    {
        self::assertSame(
            $this->companyMock,
            $this->subject->getCompany(),
        );
    }

    /**
     * @test
     */
    public function getSettingsReturnsSettings(): void
    {
        self::assertSame(
            ['foo' => 'bar'],
            $this->subject->getSettings(),
        );
    }
}
