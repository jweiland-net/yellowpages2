<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Property\TypeConverter;

use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\Exception\TypeConverterException;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Converter for uploads.
 */
class UploadOneFileConverter extends AbstractTypeConverter
{
    /**
     * @var array<string>
     */
    protected $sourceTypes = ['array'];

    /**
     * @var string
     */
    protected $targetType = FileReference::class;

    /**
     * @var int
     */
    protected $priority = 2;

    /**
     * @var ResourceFactory
     */
    protected $fileFactory;

    public function injectFileFactory(ResourceFactory $fileFactory): void
    {
        $this->fileFactory = $fileFactory;
    }

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     *
     * The return value can be one of three types:
     * - an arbitrary object, or a simple type (which has been created while mapping).
     *   This is the normal case.
     * - NULL, indicating that this object should *not* be mapped (i.e. a "File Upload" Converter could return NULL if no file has been uploaded, and a silent failure should occur.
     * - An instance of \TYPO3\CMS\Extbase\Error\Error -- This will be a user-visible error message later on.
     * Furthermore, it should throw an Exception if an unexpected failure (like a security error) occurred or a configuration issue happened.
     *
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface|null $configuration
     *
     * @return mixed|Error the target type, or an error object if a user-error occurred
     *
     * @throws TypeConverterException thrown in case a developer error occurred
     */
    public function convertFrom(
        $source,
        string $targetType,
        array $convertedChildProperties = [],
        ?PropertyMappingConfigurationInterface $configuration = null
    ) {
        /** @var \TYPO3\CMS\Extbase\Domain\Model\Filereference $alreadyPersistedImage */
        $alreadyPersistedImage = $configuration->getConfigurationValue(
            __CLASS__,
            'IMAGE'
        );

        // if no file was uploaded use the already persisted one
        if ($source['error'] === 4
            || !isset(
                $source['error'],
                $source['name'],
                $source['size'],
                $source['tmp_name'],
                $source['type']
            )
        ) {
            return $alreadyPersistedImage;
        }
        // check if uploaded file returns an error
        if ($source['error'] !== 0) {
            return new Error(
                LocalizationUtility::translate('error.upload', 'yellowpages2') . $source['error'],
                1396957314
            );
        }
        // now we have a valid uploaded file. Check if user has rights to upload this file
        if (!isset($source['rights']) || empty($source['rights'])) {
            return new Error(
                LocalizationUtility::translate('error.uploadRights', 'yellowpages2'),
                1397464390
            );
        }
        // check if file extension is allowed
        $fileParts = GeneralUtility::split_fileref($source['name']);
        if (!GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $fileParts['fileext'])) {
            return new Error(
                LocalizationUtility::translate(
                    'error.fileExtension',
                    'yellowpages2',
                    [
                        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
                    ]
                ),
                1402981282
            );
        }

        // before uploading the new file we should remove the old one
        if ($alreadyPersistedImage instanceof FileReference) {
            $alreadyPersistedImage->getOriginalResource()->delete();
        }

        return $this->getExtbaseFileReference($source);
    }

    /**
     * upload file and get a file reference object.
     *
     * @param array  $source
     *
     * @return FileReference
     */
    protected function getExtbaseFileReference(array $source): FileReference
    {
        /** @var FileReference $extbaseFileReference */
        $extbaseFileReference = $this->objectManager->get(FileReference::class);
        $extbaseFileReference->setOriginalResource($this->getCoreFileReference($source));

        return $extbaseFileReference;
    }

    /**
     * upload file and get a file reference object.
     *
     * @param array $source
     *
     * @return \TYPO3\CMS\Core\Resource\FileReference
     */
    protected function getCoreFileReference(array $source): \TYPO3\CMS\Core\Resource\FileReference
    {
        // upload file
        $uploadFolder = GeneralUtility::makeInstance(ResourceFactory::class)->retrieveFileOrFolderObject('uploads/tx_yellowpages2/');
        $uploadedFile = $uploadFolder->addUploadedFile($source, DuplicationBehavior::RENAME);
        // create Core FileReference
        return GeneralUtility::makeInstance(ResourceFactory::class)->createFileReferenceObject(
            [
                'uid_local' => $uploadedFile->getUid(),
                'uid_foreign' => uniqid('NEW_', true),
                'uid' => uniqid('NEW_', true)
            ]
        );
    }
}
