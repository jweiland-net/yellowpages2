<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Model;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

/**
 * This model represents a category (for anything).
 */
class Category extends AbstractEntity
{
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $title = '';

    protected string $description = '';

    #[Lazy]
    protected Category|LazyLoadingProxy|null $parent = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getParent(): ?Category
    {
        if ($this->parent instanceof LazyLoadingProxy) {
            $this->parent->_loadRealInstance();
        }

        return $this->parent;
    }

    public function setParent(Category $parent): void
    {
        $this->parent = $parent;
    }
}
