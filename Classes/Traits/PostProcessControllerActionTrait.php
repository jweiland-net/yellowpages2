<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Traits;

use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Event\PostProcessControllerActionEvent;

trait PostProcessControllerActionTrait
{
    protected function postProcessControllerAction(?Company $company): void
    {
        $this->eventDispatcher->dispatch(
            new PostProcessControllerActionEvent(
                $this,
                $company,
                $this->settings,
                $this->request,
            ),
        );
    }
}
