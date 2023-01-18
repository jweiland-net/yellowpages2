<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\EventListener;

use JWeiland\Yellowpages2\Event\PreProcessControllerActionEvent;

/**
 * Remove or escape security related chars from search word
 */
class SanitizeSearchWordEventListener extends AbstractControllerEventListener
{
    protected $allowedControllerActions = [
        'Company' => [
            'search',
        ],
    ];

    public function __invoke(PreProcessControllerActionEvent $event): void
    {
        if (
            $this->isValidRequest($event)
            && $event->getRequest()->hasArgument('search')
        ) {
            $search = $event->getRequest()->getArgument('search');
            $event->getRequest()->setArgument('search', htmlspecialchars($search));
        }
    }
}
