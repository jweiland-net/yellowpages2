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
use JWeiland\Yellowpages2\EventListener\ApplyUploadFolderEventListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Controller\FileUploadConfiguration;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ApplyUploadFolderEventListenerTest extends UnitTestCase
{
    private const DEFAULT_UPLOAD_FOLDER = '1:/user_upload/tx_yellowpages2/';

    private const CONFIGURED_UPLOAD_FOLDER = '2:/companies/';

    private ApplyUploadFolderEventListener $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new ApplyUploadFolderEventListener();
    }

    protected function tearDown(): void
    {
        unset($this->subject);

        parent::tearDown();
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function controllerActionsDataProvider(): array
    {
        return [
            'Management create' => ['Management', 'create', self::CONFIGURED_UPLOAD_FOLDER],
            'Management update' => ['Management', 'update', self::CONFIGURED_UPLOAD_FOLDER],
            'Management edit' => ['Management', 'edit', self::DEFAULT_UPLOAD_FOLDER],
            'Company create (removed in 9.0.0)' => ['Company', 'create', self::DEFAULT_UPLOAD_FOLDER],
            'Map update' => ['Map', 'update', self::DEFAULT_UPLOAD_FOLDER],
        ];
    }

    #[Test]
    #[DataProvider('controllerActionsDataProvider')]
    public function invokeAppliesUploadFolderForManagementController(
        string $controller,
        string $action,
        string $expectedUploadFolder,
    ): void {
        $arguments = $this->buildArguments();
        $settings = [
            'new' => [
                'uploadFolder' => self::CONFIGURED_UPLOAD_FOLDER,
            ],
        ];

        $this->subject->__invoke($this->buildEvent($controller, $action, $arguments, $settings));

        self::assertSame($expectedUploadFolder, $this->getUploadConfiguration($arguments)->getUploadFolder());
    }

    #[Test]
    public function invokeWithEmptyUploadFolderKeepsDefaultUploadFolder(): void
    {
        $arguments = $this->buildArguments();

        $this->subject->__invoke($this->buildEvent('Management', 'create', $arguments, []));

        self::assertSame(self::DEFAULT_UPLOAD_FOLDER, $this->getUploadConfiguration($arguments)->getUploadFolder());
    }

    private function buildArguments(): Arguments
    {
        $arguments = new Arguments();
        $arguments
            ->addNewArgument('company', Company::class)
            ->getFileHandlingServiceConfiguration()
            ->addFileUploadConfiguration(
                (new FileUploadConfiguration('images'))->setUploadFolder(self::DEFAULT_UPLOAD_FOLDER),
            );

        return $arguments;
    }

    private function getUploadConfiguration(Arguments $arguments): FileUploadConfiguration
    {
        $configuration = $arguments
            ->getArgument('company')
            ->getFileHandlingServiceConfiguration()
            ->getFileUploadConfigurationForProperty('images');

        self::assertInstanceOf(FileUploadConfiguration::class, $configuration);

        return $configuration;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function buildEvent(
        string $controller,
        string $action,
        Arguments $arguments,
        array $settings,
    ): PreProcessControllerActionEvent {
        $requestMock = $this->createMock(Request::class);
        $requestMock
            ->method('getControllerName')
            ->willReturn($controller);
        $requestMock
            ->method('getControllerActionName')
            ->willReturn($action);

        return new PreProcessControllerActionEvent($requestMock, $arguments, $settings);
    }
}
