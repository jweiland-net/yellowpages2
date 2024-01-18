<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Repository;

use JWeiland\Yellowpages2\Domain\Model\FeUser;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository to manage frontend users
 *
 * @method FeUser findByUid(int $frontendUserUid)
 */
class FeUserRepository extends Repository
{
}
