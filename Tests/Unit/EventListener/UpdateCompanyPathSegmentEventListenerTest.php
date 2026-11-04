<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\EventListener;

use JWeiland\Yellowpages2\Controller\ManagementController;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Event\PostProcessControllerActionEvent;
use JWeiland\Yellowpages2\EventListener\UpdateCompanyPathSegmentEventListener;
use JWeiland\Yellowpages2\Helper\PathSegmentHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class UpdateCompanyPathSegmentEventListenerTest extends UnitTestCase
{
    private PathSegmentHelper&MockObject $pathSegmentHelperMock;

    private CompanyRepository&MockObject $companyRepositoryMock;

    private PersistenceManagerInterface&MockObject $persistenceManagerMock;

    private UpdateCompanyPathSegmentEventListener $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pathSegmentHelperMock = $this->createMock(PathSegmentHelper::class);
        $this->companyRepositoryMock = $this->createMock(CompanyRepository::class);
        $this->persistenceManagerMock = $this->createMock(PersistenceManagerInterface::class);

        $this->subject = new UpdateCompanyPathSegmentEventListener(
            $this->pathSegmentHelperMock,
            $this->companyRepositoryMock,
            $this->persistenceManagerMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->pathSegmentHelperMock,
            $this->companyRepositoryMock,
            $this->persistenceManagerMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function invokeWithManagementCreateUpdatesPathSegment(): void
    {
        $company = $this->buildCompany(12);

        $this->pathSegmentHelperMock
            ->expects($this->once())
            ->method('updatePathSegmentForCompany')
            ->with($company);
        $this->companyRepositoryMock
            ->expects($this->once())
            ->method('update')
            ->with($company);
        $this->persistenceManagerMock
            ->expects($this->once())
            ->method('persistAll');

        $this->subject->__invoke($this->buildEvent('Management', 'create', $company));
    }

    /**
     * @return array<string, array{string, string, int}>
     */
    public static function invalidRequestDataProvider(): array
    {
        return [
            'Company create (removed in 9.0.0)' => ['Company', 'create', 12],
            'Management update' => ['Management', 'update', 12],
            'Management create without persisted company' => ['Management', 'create', 0],
        ];
    }

    #[Test]
    #[DataProvider('invalidRequestDataProvider')]
    public function invokeWithInvalidRequestDoesNotUpdatePathSegment(
        string $controller,
        string $action,
        int $companyUid,
    ): void {
        $this->pathSegmentHelperMock
            ->expects($this->never())
            ->method('updatePathSegmentForCompany');
        $this->persistenceManagerMock
            ->expects($this->never())
            ->method('persistAll');

        $this->subject->__invoke($this->buildEvent($controller, $action, $this->buildCompany($companyUid)));
    }

    private function buildCompany(int $uid): Company
    {
        $company = new Company();
        if ($uid > 0) {
            $company->_setProperty('uid', $uid);
        }

        return $company;
    }

    private function buildEvent(string $controller, string $action, Company $company): PostProcessControllerActionEvent
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock
            ->method('getControllerName')
            ->willReturn($controller);
        $requestMock
            ->method('getControllerActionName')
            ->willReturn($action);

        return new PostProcessControllerActionEvent(
            $this->createMock(ManagementController::class),
            $company,
            [],
            $requestMock,
        );
    }
}
