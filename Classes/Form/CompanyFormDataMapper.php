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
use JWeiland\Yellowpages2\Service\RichTextService;
use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

/**
 * Maps the submitted values of the company form to the columns of the
 * company table. The mapping is the "elements" option of the
 * SaveCompanyFinisher:
 *
 *   elements:
 *     description:
 *       mapOnDatabaseColumn: description
 *       convertPlainTextToHtml: true
 *
 * The CompanyColumnType of the column decides, how the value is stored.
 * Text values can be converted with "convertPlainTextToHtml",
 * "normalizePlainText" or "sanitizeRichText". The value of an element with
 * "poiPosition: true" (element type "Maps2PoiPosition" of EXT:maps2) is the
 * position of the POI collection of the company. Values of elements with
 * "options" (e.g. selects and checkboxes) are restricted to these options,
 * so a manipulated request can not relate other records.
 */
final readonly class CompanyFormDataMapper
{
    public function __construct(
        private CompanyColumnTypeResolver $companyColumnTypeResolver,
        private RteContentConverter $rteContentConverter,
        private RichTextService $richTextService,
    ) {}

    /**
     * @param array<string, mixed> $formValues
     * @param array<string, mixed> $elementsConfiguration
     * @param int $companyUid UID of the company to update. 0 for new companies.
     * @param int $pid Page UID of a new company
     */
    public function map(
        FormDefinition $formDefinition,
        array $formValues,
        array $elementsConfiguration,
        int $companyUid,
        int $pid,
    ): CompanyFormData {
        $data = ['columns' => [], 'categories' => [], 'files' => []];
        $position = null;
        foreach ($elementsConfiguration as $identifier => $configuration) {
            $element = $formDefinition->getElementByIdentifier((string)$identifier);
            if (!is_array($configuration) || !$element instanceof FormElementInterface || !array_key_exists($identifier, $formValues)) {
                continue;
            }

            $column = (string)($configuration['mapOnDatabaseColumn'] ?? '');
            if ((bool)($configuration['poiPosition'] ?? false)) {
                $position = PoiPosition::fromFormValue($formValues[$identifier]);
            } elseif ($column !== '') {
                $value = $this->restrictToOptions($element, $formValues[$identifier]);
                $data = $this->addValue($data, $column, $value, $configuration, $companyUid, $pid);
            }
        }

        return new CompanyFormData($data['columns'], $data['categories'], $data['files'], $position);
    }

    /**
     * @param array{columns: array<string, int|string>, categories: array<string, int[]>, files: array<string, int[]>} $data
     * @param array<string, mixed> $configuration
     * @return array{columns: array<string, int|string>, categories: array<string, int[]>, files: array<string, int[]>}
     */
    private function addValue(array $data, string $column, mixed $value, array $configuration, int $companyUid, int $pid): array
    {
        switch ($this->companyColumnTypeResolver->resolve($column)) {
            case CompanyColumnType::File:
                $data['files'][$column] = [...($data['files'][$column] ?? []), ...$this->getFileUids($value)];
                break;
            case CompanyColumnType::Category:
                $data['categories'][$column] = [...($data['categories'][$column] ?? []), ...$this->getUids($value)];
                break;
            case CompanyColumnType::Integer:
                $data['columns'][$column] = $this->getUids($value)[0] ?? 0;
                break;
            default:
                $data['columns'][$column] = $this->convertText($this->getText($value), $configuration, $column, $companyUid, $pid);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function convertText(string $text, array $configuration, string $column, int $companyUid, int $pid): string
    {
        return match (true) {
            (bool)($configuration['convertPlainTextToHtml'] ?? false) => $this->rteContentConverter->convertPlainTextToHtml($text),
            (bool)($configuration['normalizePlainText'] ?? false) => $this->rteContentConverter->normalizePlainText($text),
            (bool)($configuration['sanitizeRichText'] ?? false) => $this->richTextService->prepareForPersistence(
                $text,
                CompanyColumnTypeResolver::TABLE,
                $column,
                $companyUid,
                $pid,
            ),
            default => trim($text),
        };
    }

    private function restrictToOptions(FormElementInterface $element, mixed $value): mixed
    {
        $options = $element->getProperties()['options'] ?? [];
        if (!is_array($options) || $options === []) {
            return $value;
        }

        $isOption = static fn(mixed $item): bool => is_scalar($item) && array_key_exists((string)$item, $options);
        if (is_array($value)) {
            return array_values(array_filter($value, $isOption));
        }

        return $isOption($value) ? $value : '';
    }

    /**
     * @return int[]
     */
    private function getUids(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];

        return array_values(array_filter(
            array_map(static fn(mixed $item): int => is_numeric($item) ? (int)$item : 0, $values),
            static fn(int $uid): bool => $uid > 0,
        ));
    }

    /**
     * @return int[]
     */
    private function getFileUids(mixed $value): array
    {
        $fileReference = $value instanceof FileReference ? $value->getOriginalResource() : $value;

        return $fileReference instanceof CoreFileReference ? [$fileReference->getOriginalFile()->getUid()] : [];
    }

    private function getText(mixed $value): string
    {
        if (is_array($value)) {
            return implode(',', array_filter($value, is_scalar(...)));
        }

        return is_scalar($value) ? (string)$value : '';
    }
}
