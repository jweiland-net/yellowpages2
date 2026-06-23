<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Traits;

use JWeiland\Yellowpages2\Event\PreProcessControllerActionEvent;

trait PreProcessControllerActionTrait
{
    protected function preProcessControllerAction(): void
    {
        $actionEvent = new PreProcessControllerActionEvent(
            $this->request,
            $this->arguments,
            $this->settings,
        );
        $this->eventDispatcher->dispatch($actionEvent);

        $this->request = $actionEvent->getRequest();
        $this->arguments = $actionEvent->getArguments();
        $this->actionMethodName = $this->resolveActionMethodName();
    }
}
