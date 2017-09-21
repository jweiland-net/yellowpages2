<?php
declare(strict_types=1);
namespace JWeiland\Yellowpages2\Domain\Model;

/*
 * This file is part of the TYPO3 CMS project.
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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class District extends AbstractEntity
{
    /**
     * District
     *
     * @var string
     */
    protected $district = '';

    /**
     * Returns the district
     *
     * @return string $district
     */
    public function getDistrict(): string
    {
        return $this->district;
    }

    /**
     * Sets the district
     *
     * @param string $district
     * @return void
     */
    public function setDistrict(string $district)
    {
        $this->district = $district;
    }
}
