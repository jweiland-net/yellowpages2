<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\EventListener;

use JWeiland\Yellowpages2\Event\ControllerActionEventInterface;

/**
 * Abstract EventListener just for action controllers.
 */
class AbstractControllerEventListener
{
    /**
     * Only execute this EventListener if controller and action matches
     *
     * @var array
     */
    protected $allowedControllerActions = [];

    protected function isValidRequest(ControllerActionEventInterface $event): bool
    {
        return
            array_key_exists(
                $event->getControllerName(),
                $this->allowedControllerActions
            )
            && in_array(
                $event->getActionName(),
                $this->allowedControllerActions[$event->getControllerName()],
                true
            );
    }
}
