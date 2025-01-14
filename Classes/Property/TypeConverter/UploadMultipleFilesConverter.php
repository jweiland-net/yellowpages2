<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Property\TypeConverter;

use JWeiland\Checkfaluploads\Service\FalUploadService;
use JWeiland\Yellowpages2\Event\PostCheckFileReferenceEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

/*
 * A for PropertyMapper to convert multiple file uploads into an array
 */
class UploadMultipleFilesConverter extends AbstractTypeConverter
{
    private const DEFAULT_UPLOAD_FOLDER = '1:/user_upload/';

    private const CONFIGURATION_IMAGES = 'IMAGES';

    private const CONFIGURATION_SETTINGS = 'settings';

    protected array $sourceTypes = [UploadedFile::class, 'array'];

    protected string $targetType = ObjectStorage::class;

    protected int $priority = 2;

    protected ?Folder $uploadFolder = null;

    protected ?FalUploadService $falUploadService = null;

    protected array|PropertyMappingConfigurationInterface $converterConfiguration = [];

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ResourceFactory $resourceFactory,
    ) {}

    /**
     * This implementation always returns TRUE for this method.
     * return true if this TypeConverter can convert from $source to $targetType, FALSE otherwise.
     *
     * @param mixed  $source     the source data
     * @param string $targetType the type to convert to.
     */
    public function canConvertFrom(mixed $source, string $targetType): bool
    {
        if (is_array($source)) {
            foreach ($source as $uploadedFile) {
                if (!$uploadedFile instanceof UploadedFile) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function convertFrom(
        $source,
        string $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null,
    ) {
        $this->initialize($configuration);

        $references = new ObjectStorage();

        foreach ($source as $key => $uploadedFile) {
            if (!$uploadedFile instanceof UploadedFile || !$this->isValidUploadFile($uploadedFile)) {
                continue;
            }

            if (
                ExtensionManagementUtility::isLoaded('checkfaluploads')
                && $error = $this->getFalUploadService()->checkFile($uploadedFile)
            ) {
                return $error;
            }

            // Dispatch post-upload event
            $this->eventDispatcher->dispatch(
                new PostCheckFileReferenceEvent(
                    $source,
                    $key,
                    $this->getPersistedFileByPosition($key),
                    $uploadedFile,
                ),
            );

            // Upload and add file reference
            $references->attach($this->getExtbaseFileReference($uploadedFile));
        }

        return $references;
    }

    private function initialize(?PropertyMappingConfigurationInterface $configuration): void
    {
        if (!$configuration instanceof PropertyMappingConfigurationInterface) {
            throw new \InvalidArgumentException(
                'Missing PropertyMapper configuration in UploadMultipleFilesConverter',
                1605617449,
            );
        }

        $this->converterConfiguration = $configuration;
        $this->uploadFolder = $this->resolveUploadFolder($configuration);
    }

    private function resolveUploadFolder(PropertyMappingConfigurationInterface $configuration): Folder
    {
        $uploadFolderIdentifier = $configuration->getConfigurationValue(
            self::class,
            self::CONFIGURATION_SETTINGS . '.uploadFolder',
        ) ?? self::DEFAULT_UPLOAD_FOLDER;

        try {
            return $this->resourceFactory->getFolderObjectFromCombinedIdentifier($uploadFolderIdentifier);
        } catch (ResourceDoesNotExistException $e) {
            throw new \InvalidArgumentException('Invalid upload folder configuration.', 0, $e);
        }
    }

    protected function isValidUploadFile(UploadedFile $uploadedFile): bool
    {
        return $uploadedFile->getError() === UPLOAD_ERR_OK;
    }

    /**
     * Do not use constructor injection for that class as EXT:checkfaluploads might not be loaded
     */
    protected function getFalUploadService(): FalUploadService
    {
        if ($this->falUploadService === null) {
            $this->falUploadService = GeneralUtility::makeInstance(FalUploadService::class);
        }

        return $this->falUploadService;
    }

    private function getPersistedFileByPosition(int $position): ?FileReference
    {
        $persistedFiles = $this->converterConfiguration->getConfigurationValue(
            self::class,
            self::CONFIGURATION_IMAGES,
        );

        return $persistedFiles instanceof ObjectStorage ? $persistedFiles->offsetGet($position) : null;
    }

    private function getExtbaseFileReference(UploadedFile $file): FileReference
    {
        $coreFileReference = $this->uploadFile($file);
        $extbaseFileReference = GeneralUtility::makeInstance(FileReference::class);
        $extbaseFileReference->setOriginalResource($coreFileReference);

        return $extbaseFileReference;
    }

    private function uploadFile(UploadedFile $file): \TYPO3\CMS\Core\Resource\FileReference
    {
        $uploadedFile = $this->uploadFolder->addUploadedFile(
            [
                'tmp_name' => $file->getStream()->getMetadata('uri'),
                'name' => $file->getClientFilename(),
                'type' => $file->getClientMediaType(),
                'error' => $file->getError(),
                'size' => $file->getSize(),
            ],
            DuplicationBehavior::RENAME,
        );

        return $this->resourceFactory->createFileReferenceObject(
            [
                'uid_local' => $uploadedFile->getUid(),
                'uid_foreign' => uniqid('NEW_', true),
                'uid' => uniqid('NEW_', true),
            ],
        );
    }
}
