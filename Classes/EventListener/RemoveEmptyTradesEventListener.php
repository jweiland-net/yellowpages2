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
 * Remove empty trades from request to prevent errors while storing/updating the FE record
 */
class RemoveEmptyTradesEventListener extends AbstractControllerEventListener
{
    protected $allowedControllerActions = [
        'Company' => [
            'create',
            'update',
        ],
    ];

    public function __invoke(PreProcessControllerActionEvent $event): void
    {
        if (
            $this->isValidRequest($event)
            && $event->getRequest()->hasArgument('company')
        ) {
            $company = $event->getRequest()->getArgument('company');
            $company['trades'] = array_filter($company['trades']);
            $event->getRequest()->setArgument('company', $company);
        }
    }
}
