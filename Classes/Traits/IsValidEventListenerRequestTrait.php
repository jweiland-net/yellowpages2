<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Traits;

use JWeiland\Yellowpages2\Event\ControllerActionEventInterface;

/**
 * Trait to validate if the incoming request is valid for a specific EventListener
 */
trait IsValidEventListenerRequestTrait
{
    protected function isValidRequest(ControllerActionEventInterface $event): bool
    {
        return
            array_key_exists($event->getControllerName(), self::ALLOWED_CONTROLLER_ACTIONS)
            && in_array(
                $event->getActionName(),
                self::ALLOWED_CONTROLLER_ACTIONS[$event->getControllerName()],
                true,
            );
    }
}
