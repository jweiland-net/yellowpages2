<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\EventListener;

use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Event\PostProcessControllerActionEvent;
use JWeiland\Yellowpages2\EventListener\UpdateCompanyPathSegmentEventListener;
use JWeiland\Yellowpages2\Helper\PathSegmentHelper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
class UpdateCompanyPathSegmentEventListenerTest extends UnitTestCase
{
    protected UpdateCompanyPathSegmentEventListener $subject;

    /**
     * @var PathSegmentHelper|MockObject
     */
    protected $pathSegmentHelperMock;

    /**
     * @var ActionController|MockObject
     */
    protected $controllerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pathSegmentHelperMock = $this->createMock(PathSegmentHelper::class);
        $this->controllerMock = $this->createMock(ActionController::class);

        $this->subject = new UpdateCompanyPathSegmentEventListener(
            $this->pathSegmentHelperMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->pathSegmentHelperMock,
            $this->controllerMock,
        );

        parent::tearDown();
    }

    private function getEvent(string $controllerName, string $actionName, ?Company $company): PostProcessControllerActionEvent
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock
            ->method('getControllerName')
            ->willReturn($controllerName);
        $requestMock
            ->method('getControllerActionName')
            ->willReturn($actionName);

        return new PostProcessControllerActionEvent(
            $this->controllerMock,
            $company,
            [],
            $requestMock,
        );
    }

    #[Test]
    public function invokeUpdatesPathSegmentOfNewCompanyOnCreateAction(): void
    {
        $company = new Company();

        $this->pathSegmentHelperMock
            ->expects(self::once())
            ->method('updatePathSegmentForCompany')
            ->with($company);

        $this->subject->__invoke($this->getEvent('Company', 'create', $company));
    }

    #[Test]
    public function invokeDoesNotUpdatePathSegmentOnOtherActions(): void
    {
        $this->pathSegmentHelperMock
            ->expects(self::never())
            ->method('updatePathSegmentForCompany');

        $this->subject->__invoke($this->getEvent('Company', 'update', new Company()));
    }

    #[Test]
    public function invokeDoesNotUpdatePathSegmentOnOtherControllers(): void
    {
        $this->pathSegmentHelperMock
            ->expects(self::never())
            ->method('updatePathSegmentForCompany');

        $this->subject->__invoke($this->getEvent('Map', 'create', new Company()));
    }

    #[Test]
    public function invokeDoesNotUpdatePathSegmentIfCompanyIsNull(): void
    {
        $this->pathSegmentHelperMock
            ->expects(self::never())
            ->method('updatePathSegmentForCompany');

        $this->subject->__invoke($this->getEvent('Company', 'create', null));
    }
}
