<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Service;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Yellowpages2\Domain\Model\Company;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Utility\GeneralUtility;

readonly class LocationService
{
    public function createPoiForCompany(Company $company, FlashMessageQueue $flashMessageQueue): bool
    {
        $geoCodeService = GeneralUtility::makeInstance(GeoCodeService::class);
        // Note: Ideally, GeoCodeService should be injected, but if it's external and strictly static/utility based,
        // makeInstance is acceptable in legacy transitions.

        $position = $geoCodeService->getFirstFoundPositionByAddress($company->getAddress());

        if ($position instanceof Position) {
            $poi = new PoiCollection(); // Entities should be newed up
            $poi->setCollectionType('Point');
            $poi->setTitle($company->getCompany());
            $poi->setAddress($position->getFormattedAddress());
            $poi->setLatitude($position->getLatitude());
            $poi->setLongitude($position->getLongitude());

            $company->setTxMaps2Uid($poi);

            return true;
        }

        $flashMessageQueue->enqueue(...$geoCodeService->getErrors());

        return false;
    }
}
