<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Form;

/**
 * The values of the company form prepared for the database
 */
final readonly class CompanyFormData
{
    /**
     * @param array<string, int|string> $columns Values of the columns of the company table
     * @param array<string, int[]> $categories UIDs of sys_category for each category column
     * @param array<string, int[]> $files UIDs of sys_file for each file column
     * @param PoiPosition|null $position Position of the company on the map. Null, if not submitted.
     */
    public function __construct(
        public array $columns = [],
        public array $categories = [],
        public array $files = [],
        public ?PoiPosition $position = null,
    ) {}

    /**
     * Static values like "pid" or "hidden". Values of form elements take precedence.
     *
     * @param array<string, int|string> $columns
     */
    public function withAdditionalColumns(array $columns): self
    {
        return new self(array_replace($columns, $this->columns), $this->categories, $this->files, $this->position);
    }
}
