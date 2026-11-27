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
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/*
 * Helper class to register hidden objects in extbase session container.
 * That way it's possible to call Controller Actions with hidden objects.
 */

final readonly class HiddenObjectHelper
{
    public function __construct(
        private Session $session,
    ) {}

    /**
     * @template T of object
     * @param RepositoryInterface<T> $repository
     */
    public function registerHiddenObjectInExtbaseSession(
        RepositoryInterface $repository,
        RequestInterface $request,
        string $argumentName,
    ): void {
        if (!$repository instanceof HiddenRepositoryInterface) {
            return;
        }

        $objectRaw = $request->getArgument($argumentName);
        // Either the object from a form ($_POST) or the object from its UID
        $object = $repository->findHiddenObject(
            is_array($objectRaw) ? (int)($objectRaw['__identity'] ?? 0) : (int)$objectRaw,
        );

        if ($object instanceof AbstractDomainObject) {
            $this->session->registerObject($object, (string)$object->getUid());
        }
    }
}
