<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * A repository to retrieve districts.
 * Needed for selectbox in frontend
 */
class DistrictRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'district' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * Get all districts for selectbox
     */
    public function getDistricts(): array
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
