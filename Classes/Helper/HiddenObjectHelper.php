<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Helper;

use JWeiland\Yellowpages2\Domain\Repository\HiddenRepositoryInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/*
 * Helper class to register hidden objects in extbase session container.
 * That way it's possible to call Controller Actions with hidden objects.
 */

class HiddenObjectHelper
{
    public function __construct(
        protected readonly Session $session
    ) {}

    public function registerHiddenObjectInExtbaseSession(
        RepositoryInterface $repository,
        RequestInterface $request,
        string $argumentName
    ): void {
        // Ensure the repository supports hidden objects
        if (!$repository instanceof HiddenRepositoryInterface) {
            return;
        }

        // Get the raw object data from the request
        $objectRaw = $request->getArgument($argumentName);

        // Resolve the object based on the raw data
        $this->resolveHiddenObject($repository, $objectRaw);

        // Register the resolved object in the session if it is valid
        if ($object !== null) {
            $this->session->registerObject($object, $object->getUid());
        }
    }

    private function resolveHiddenObject(
        HiddenRepositoryInterface $repository,
        mixed $objectRaw
    ): void {
        // Handle raw data from form (array) or UID (integer/string)
        if (is_array($objectRaw) && isset($objectRaw['__identity'])) {
            $repository->findHiddenObject((int)$objectRaw['__identity']);
            return;
        }

        if (is_numeric($objectRaw)) {
            $repository->findHiddenObject((int)$objectRaw);
        }
    }
}
