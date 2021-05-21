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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;

class AssignMediaTypeConverterEventListener extends AbstractControllerEventListener
{
    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

    protected $allowedControllerActions = [
        'Company' => [
            'create',
            'update'
        ]
    ];

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

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

    protected function assignTypeConverterForCreateAction(PreProcessControllerActionEvent $event): void
    {
        $this->setTypeConverterForProperty('logo', null, $event);
        $this->setTypeConverterForProperty('images', null, $event);
    }

    protected function assignTypeConverterForUpdateAction(PreProcessControllerActionEvent $event): void
    {
        // Needed to get the previously stored logo and images
        /** @var Company $persistedCompany */
        $persistedCompany = $this->companyRepository->findByIdentifier(
            $event->getRequest()->getArgument('company')['__identity']
        );

        $this->setTypeConverterForProperty('logo', $persistedCompany->getOriginalLogo(), $event);
        $this->setTypeConverterForProperty('images', $persistedCompany->getOriginalImages(), $event);
    }

    protected function setTypeConverterForProperty(
        string $property,
        ?ObjectStorage $persistedFiles,
        PreProcessControllerActionEvent $event
    ): void {
        $propertyMappingConfiguration = $this->getPropertyMappingConfigurationForCompany($event)
            ->forProperty($property)
            ->setTypeConverter(GeneralUtility::makeInstance(UploadMultipleFilesConverter::class));

        // Do not use setTypeConverterOptions() as this will remove all existing options
        $this->addOptionToUploadFilesConverter(
            $propertyMappingConfiguration,
            'settings',
            $event->getSettings()
        );

        if ($persistedFiles !== null) {
            $this->addOptionToUploadFilesConverter(
                $propertyMappingConfiguration,
                'IMAGES',
                $persistedFiles
            );
        }
    }

    protected function getPropertyMappingConfigurationForCompany(
        PreProcessControllerActionEvent $event
    ): MvcPropertyMappingConfiguration {
        return $event->getArguments()
            ->getArgument('company')
            ->getPropertyMappingConfiguration();
    }

    protected function addOptionToUploadFilesConverter(
        PropertyMappingConfiguration $propertyMappingConfiguration,
        string $optionKey,
        $optionValue
    ): void {
        $propertyMappingConfiguration->setTypeConverterOption(
            UploadMultipleFilesConverter::class,
            $optionKey,
            $optionValue
        );
    }
}
