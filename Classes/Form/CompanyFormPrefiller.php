<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Form;

use JWeiland\Yellowpages2\Converter\RteContentConverter;
use JWeiland\Yellowpages2\Service\CategoryRelationService;
use JWeiland\Yellowpages2\Service\CompanyAccessService;
use JWeiland\Yellowpages2\Service\CompanyPoiCollectionService;
use JWeiland\Yellowpages2\Service\CompanyRecordService;
use JWeiland\Yellowpages2\Service\FileReferenceService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

/**
 * Prefills the company form with the values of the company to edit. Each
 * form element with "properties.dbMapping.column" gets the value of this
 * column. "properties.dbMapping.dataType" defines the conversion:
 *
 * - "plainText": HTML of the RTE is converted into plain text
 * - "richText": The rich text editor gets the stored HTML
 * - "poiPosition": The position of the POI collection (EXT:maps2) of the
 *   column, e.g. "tx_maps2_uid", for the element "Maps2PoiPosition"
 *
 * Category columns are prefilled with the UIDs of the related categories
 * (an array for MultiCheckbox elements). File columns are prefilled with
 * the file reference at "properties.dbMapping.position" (default: 1).
 *
 * The access is checked again, as the form could be rendered outside of the
 * ManagementController.
 */
final readonly class CompanyFormPrefiller
{
    public const DATA_TYPE_PLAIN_TEXT = 'plainText';

    public const DATA_TYPE_POI_POSITION = 'poiPosition';

    private const MULTI_VALUE_TYPES = ['MultiCheckbox', 'MultiSelect'];

    public function __construct(
        private CompanyAccessService $companyAccessService,
        private CompanyRecordService $companyRecordService,
        private CompanyColumnTypeResolver $companyColumnTypeResolver,
        private CategoryRelationService $categoryRelationService,
        private FileReferenceService $fileReferenceService,
        private CompanyPoiCollectionService $companyPoiCollectionService,
        private ResourceFactory $resourceFactory,
        private RteContentConverter $rteContentConverter,
        private LoggerInterface $logger,
    ) {}

    public function prefill(FormDefinition $formDefinition, ServerRequestInterface $request): void
    {
        $companyUid = $this->companyAccessService->getCompanyUid($request);
        if ($companyUid === 0 || $this->companyAccessService->getDenialReason($request) !== '') {
            return;
        }

        $company = $this->companyRecordService->findCompany($companyUid);
        foreach ($company === [] ? [] : $formDefinition->getRenderablesRecursively() as $element) {
            $dbMapping = $element instanceof FormElementInterface ? $element->getProperties()['dbMapping'] ?? [] : [];
            $column = is_array($dbMapping) ? (string)($dbMapping['column'] ?? '') : '';
            if ($column === '' || !$element instanceof FormElementInterface || !array_key_exists($column, $company)) {
                continue;
            }

            $value = $this->getDefaultValue($element, $dbMapping, $company);
            if ($value !== null) {
                $element->setDefaultValue($value);
            }
        }
    }

    /**
     * @param array<string, mixed> $dbMapping
     * @param array<string, mixed> $company
     */
    private function getDefaultValue(FormElementInterface $element, array $dbMapping, array $company): mixed
    {
        $column = (string)$dbMapping['column'];
        if (($dbMapping['dataType'] ?? '') === self::DATA_TYPE_POI_POSITION) {
            return $this->companyPoiCollectionService->getPosition((int)$company[$column])?->toFormValue();
        }

        return match ($this->companyColumnTypeResolver->resolve($column)) {
            CompanyColumnType::File => $this->getFileReference(
                (int)$company['uid'],
                $column,
                max(1, (int)($dbMapping['position'] ?? 1)),
            ),
            CompanyColumnType::Category => $this->getCategoryValue($element, (int)$company['uid'], $column),
            default => $this->getColumnValue($company[$column], (string)($dbMapping['dataType'] ?? '')),
        };
    }

    private function getColumnValue(mixed $value, string $dataType): string
    {
        $value = is_scalar($value) ? (string)$value : '';

        return $dataType === self::DATA_TYPE_PLAIN_TEXT ? $this->rteContentConverter->convertHtmlToPlainText($value) : $value;
    }

    /**
     * @return string[]|string
     */
    private function getCategoryValue(FormElementInterface $element, int $companyUid, string $column): array|string
    {
        $categoryUids = array_map(strval(...), $this->categoryRelationService->getCategoryUids($companyUid, $column));

        return in_array($element->getType(), self::MULTI_VALUE_TYPES, true) ? $categoryUids : ($categoryUids[0] ?? '');
    }

    /**
     * EXT:form expects an Extbase file reference as value of upload elements
     */
    private function getFileReference(int $companyUid, string $column, int $position): ?FileReference
    {
        $referenceUid = array_keys($this->fileReferenceService->getFileUids($companyUid, $column))[$position - 1] ?? 0;
        if ($referenceUid === 0) {
            return null;
        }

        try {
            $fileReference = new FileReference();
            $fileReference->setOriginalResource($this->resourceFactory->getFileReferenceObject($referenceUid));
        } catch (ResourceDoesNotExistException $exception) {
            $this->logger->warning(
                'The file of a file reference of the company does not exist. The upload element stays empty.',
                [
                    'companyUid' => $companyUid,
                    'column' => $column,
                    'fileReferenceUid' => $referenceUid,
                    'exception' => $exception,
                ],
            );

            return null;
        }

        return $fileReference;
    }
}
