<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\EventListener;

use JWeiland\Checkfaluploads\Validation\Validator\CheckFalUploadValidator;
use JWeiland\Yellowpages2\Event\PreProcessControllerActionEvent;
use JWeiland\Yellowpages2\Traits\IsValidEventListenerRequestTrait;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

#[AsEventListener(
    identifier: 'yellowpages2/check-fal-uploads',
)]
final readonly class CheckFalUploadsEventListener
{
    use IsValidEventListenerRequestTrait;

    private const ALLOWED_CONTROLLER_ACTIONS = [
        'Company' => [
            'create',
            'update',
        ],
    ];

    private const VALIDATOR = CheckFalUploadValidator::class;

    private const PROPERTY_NAMES = [
        'logo',
        'images',
    ];

    public function __construct(
        private ValidatorResolver $validatorResolver,
    ) {}

    public function __invoke(PreProcessControllerActionEvent $controllerActionEvent): void
    {
        if (!ExtensionManagementUtility::isLoaded('checkfaluploads')) {
            return;
        }

        if (!$this->isValidRequest($controllerActionEvent)) {
            return;
        }

        $uploadedFiles = $controllerActionEvent->getRequest()->getUploadedFiles();
        foreach (self::PROPERTY_NAMES as $propertyName) {
            if (!isset($uploadedFiles['tx_yellowpages2_directory']['company'][$propertyName])) {
                continue;
            }

            $this->assignValidatorToFileUploadConfiguration($controllerActionEvent, $propertyName);
        }
    }

    private function assignValidatorToFileUploadConfiguration(
        PreProcessControllerActionEvent $controllerActionEvent,
        string $propertyName,
    ): void {
        $checkFalUploadsValidator = $this->getValidator(
            self::VALIDATOR,
            ['propertyPath' => 'company.' . $propertyName],
            $controllerActionEvent->getRequest(),
        );

        $argument = $controllerActionEvent->getArguments()->getArgument('company');
        $configuration = $argument
            ->getFileHandlingServiceConfiguration()
            ->getFileUploadConfigurationForProperty($propertyName);
        $configuration?->addValidator($checkFalUploadsValidator);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function getValidator(
        string $className,
        array $options,
        RequestInterface $request,
    ): ValidatorInterface {
        return $this->validatorResolver->createValidator($className, $options, $request);
    }
}
