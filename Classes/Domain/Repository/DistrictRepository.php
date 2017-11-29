<?php
declare(strict_types=1);
namespace JWeiland\Yellowpages2\Domain\Repository;

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

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DistrictRepository extends Repository
{

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'district' => QueryInterface::ORDER_ASCENDING
    ];

    /**
     * get all districts for selectbox
     *
     * @return array
     */
    public function getDistricts()
    {
        $query = $this->createQuery();
        $results = $query->execute(true);
        $districts = [];
        foreach ($results as $result) {
            $districts[$result['uid']] = $result['district'];
        }
        return $districts;
    }
}
