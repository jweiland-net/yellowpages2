<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Form;

use TYPO3\CMS\Core\Schema\TcaSchemaFactory;

/**
 * Resolves the CompanyColumnType of a column of the company table from TCA
 */
final readonly class CompanyColumnTypeResolver
{
    public const TABLE = 'tx_yellowpages2_domain_model_company';

    private const CATEGORY_MM_TABLE = 'sys_category_record_mm';

    private const INTEGER_TYPES = ['check', 'number', 'select'];

    public function __construct(
        private TcaSchemaFactory $tcaSchemaFactory,
    ) {}

    public function resolve(string $column): CompanyColumnType
    {
        $configuration = $this->getFieldConfiguration($column);
        $type = (string)($configuration['type'] ?? '');

        return match (true) {
            $type === 'file' => CompanyColumnType::File,
            ($configuration['MM'] ?? '') === self::CATEGORY_MM_TABLE => CompanyColumnType::Category,
            in_array($type, self::INTEGER_TYPES, true) => CompanyColumnType::Integer,
            default => CompanyColumnType::Text,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function getFieldConfiguration(string $column): array
    {
        if (!$this->tcaSchemaFactory->has(self::TABLE)) {
            return [];
        }

        $schema = $this->tcaSchemaFactory->get(self::TABLE);

        return $schema->hasField($column) ? $schema->getField($column)->getConfiguration() : [];
    }
}
