<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\EventListener;

use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Event\PreProcessControllerActionEvent;
use JWeiland\Yellowpages2\Property\TypeConverter\UploadMultipleFilesConverter;
use JWeiland\Yellowpages2\Traits\IsValidEventListenerRequestTrait;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;

/**
 * Build up special configuration for media files in property mapper
 */
#[AsEventListener(
    identifier: 'yellowpages2/assign-media-type-converter',
)]
final readonly class AssignMediaTypeConverterEventListener
{
    use IsValidEventListenerRequestTrait;

    protected const ALLOWED_CONTROLLER_ACTIONS = [
        'Company' => [
            'create',
            'update',
        ],
    ];

    public function __construct(
        private CompanyRepository $companyRepository,
    ) {}

    public function __invoke(PreProcessControllerActionEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            if ($event->getActionName() === 'create') {
                $this->assignTypeConverterForCreateAction($event);
            } else {
                $this->assignTypeConverterForUpdateAction($event);
            }
        }
    }

    private function assignTypeConverterForCreateAction(PreProcessControllerActionEvent $event): void
    {
        $this->setTypeConverterForProperty('logo', null, $event);
        $this->setTypeConverterForProperty('images', null, $event);
    }

    private function assignTypeConverterForUpdateAction(PreProcessControllerActionEvent $event): void
    {
        // Needed to get the previously stored logo and images
        /** @var Company $persistedCompany */
        $persistedCompany = $this->companyRepository->findByIdentifier(
            $event->getRequest()->getArgument('company')['__identity'],
        );

        if ($persistedCompany instanceof Company) {
            $this->setTypeConverterForProperty('logo', $persistedCompany->getOriginalLogo(), $event);
            $this->setTypeConverterForProperty('images', $persistedCompany->getOriginalImages(), $event);
        }
    }

    private function setTypeConverterForProperty(
        string $property,
        ?ObjectStorage $persistedFiles,
        PreProcessControllerActionEvent $event,
    ): void {
        $propertyMappingConfiguration = $this->getPropertyMappingConfigurationForCompany($event)
            ->forProperty($property)
            ->setTypeConverter(GeneralUtility::makeInstance(UploadMultipleFilesConverter::class));

        // Do not use setTypeConverterOptions() as this will remove all existing options
        $this->addOptionToUploadFilesConverter(
            $propertyMappingConfiguration,
            'settings',
            $event->getSettings(),
        );

        if ($persistedFiles instanceof ObjectStorage) {
            $this->addOptionToUploadFilesConverter(
                $propertyMappingConfiguration,
                'IMAGES',
                $persistedFiles,
            );
        }
    }

    private function getPropertyMappingConfigurationForCompany(
        PreProcessControllerActionEvent $event,
    ): MvcPropertyMappingConfiguration {
        return $event->getArguments()
            ->getArgument('company')
            ->getPropertyMappingConfiguration();
    }

    private function addOptionToUploadFilesConverter(
        PropertyMappingConfiguration $propertyMappingConfiguration,
        string $optionKey,
        $optionValue,
    ): void {
        $propertyMappingConfiguration->setTypeConverterOption(
            UploadMultipleFilesConverter::class,
            $optionKey,
            $optionValue,
        );
    }
}
