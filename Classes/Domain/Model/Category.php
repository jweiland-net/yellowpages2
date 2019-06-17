<?php
declare(strict_types=1);
namespace JWeiland\Yellowpages2\Domain\Model;

/*
 * This file is part of the yellowpages2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Domain model for categories.
 *
 * As TYPO3 does not come with TCA nor with a SQL entry, it is not save
 * to use the icon setter/getter of extbase. Maybe they will be removed
 * in future.
 */
class Category extends \TYPO3\CMS\Extbase\Domain\Model\Category
{
    /**
     * Icon
     *
     * @var string
     */
    protected $icon = '';

    /**
     * Returns the icon
     *
     * @return string $icon
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Sets the icon
     *
     * @param string $icon
     * @return void
     */
    public function setIcon($icon)
    {
        $this->icon = (string)$icon;
    }
}
