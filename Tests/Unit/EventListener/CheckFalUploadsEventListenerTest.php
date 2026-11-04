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
use JWeiland\Yellowpages2\Event\PreProcessControllerActionEvent;
use JWeiland\Yellowpages2\EventListener\CheckFalUploadsEventListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Controller\FileUploadConfiguration;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class CheckFalUploadsEventListenerTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private ValidatorInterface&MockObject $validator;

    private CheckFalUploadsEventListener $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->createMock(ValidatorInterface::class);

        $validatorResolverMock = $this->createMock(ValidatorResolver::class);
        $validatorResolverMock
            ->method('createValidator')
            ->willReturn($this->validator);

        $this->subject = new CheckFalUploadsEventListener($validatorResolverMock);
    }

    protected function tearDown(): void
    {
        unset($this->subject, $this->validator);

        parent::tearDown();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function validControllerActionsDataProvider(): array
    {
        return [
            'Management create' => ['Management', 'create'],
            'Management update' => ['Management', 'update'],
        ];
    }

    #[Test]
    #[DataProvider('validControllerActionsDataProvider')]
    public function invokeAddsValidatorForManagementController(string $controller, string $action): void
    {
        $this->setCheckfaluploadsLoaded(true);
        $arguments = $this->buildArguments();

        $this->subject->__invoke($this->buildEvent($controller, $action, $arguments));

        foreach (['logo', 'images'] as $propertyName) {
            self::assertSame(
                [$this->validator],
                $this->getUploadConfiguration($arguments, $propertyName)->getValidators(),
            );
        }
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidControllerActionsDataProvider(): array
    {
        return [
            'Company create (removed in 9.0.0)' => ['Company', 'create'],
            'Company update (removed in 9.0.0)' => ['Company', 'update'],
            'Management edit' => ['Management', 'edit'],
            'Map create' => ['Map', 'create'],
        ];
    }

    #[Test]
    #[DataProvider('invalidControllerActionsDataProvider')]
    public function invokeWithInvalidControllerActionDoesNotAddValidator(string $controller, string $action): void
    {
        $this->setCheckfaluploadsLoaded(true);
        $arguments = $this->buildArguments();

        $this->subject->__invoke($this->buildEvent($controller, $action, $arguments));

        self::assertSame([], $this->getUploadConfiguration($arguments, 'images')->getValidators());
    }

    #[Test]
    public function invokeWithoutCheckfaluploadsDoesNotAddValidator(): void
    {
        $this->setCheckfaluploadsLoaded(false);
        $arguments = $this->buildArguments();

        $this->subject->__invoke($this->buildEvent('Management', 'create', $arguments));

        self::assertSame([], $this->getUploadConfiguration($arguments, 'images')->getValidators());
    }

    private function setCheckfaluploadsLoaded(bool $isLoaded): void
    {
        $packageManagerMock = $this->createMock(PackageManager::class);
        $packageManagerMock
            ->method('isPackageActive')
            ->willReturnCallback(
                static fn(string $packageKey): bool => $packageKey === 'checkfaluploads' && $isLoaded,
            );

        ExtensionManagementUtility::setPackageManager($packageManagerMock);
    }

    private function buildArguments(): Arguments
    {
        $arguments = new Arguments();
        $argument = $arguments->addNewArgument('company', Company::class);
        foreach (['logo', 'images'] as $propertyName) {
            $argument
                ->getFileHandlingServiceConfiguration()
                ->addFileUploadConfiguration(new FileUploadConfiguration($propertyName));
        }

        return $arguments;
    }

    private function getUploadConfiguration(Arguments $arguments, string $propertyName): FileUploadConfiguration
    {
        $configuration = $arguments
            ->getArgument('company')
            ->getFileHandlingServiceConfiguration()
            ->getFileUploadConfigurationForProperty($propertyName);

        self::assertInstanceOf(FileUploadConfiguration::class, $configuration);

        return $configuration;
    }

    private function buildEvent(string $controller, string $action, Arguments $arguments): PreProcessControllerActionEvent
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock
            ->method('getControllerName')
            ->willReturn($controller);
        $requestMock
            ->method('getControllerActionName')
            ->willReturn($action);

        return new PreProcessControllerActionEvent($requestMock, $arguments, []);
    }
}
