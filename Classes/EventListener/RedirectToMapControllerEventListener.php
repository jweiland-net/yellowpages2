<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\EventListener;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Event\PostProcessControllerActionEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class RedirectToMapControllerEventListener extends AbstractControllerEventListener
{
    protected $allowedControllerActions = [
        'Company' => [
            'create',
            'update'
        ]
    ];

    public function __invoke(PostProcessControllerActionEvent $event): void
    {
        if (
            $this->isValidRequest($event)
            && $event->getCompany() instanceof Company
            && ExtensionManagementUtility::isLoaded('maps2')
        ) {
            $mapControllerActionName = 'new';
            if (
                $event->getActionName() === 'update'
                && $event->getCompany()->getTxMaps2Uid() instanceof PoiCollection
            ) {
                $mapControllerActionName = 'edit';
            }

            $event->getCompanyController()->redirect(
                $mapControllerActionName,
                'Map',
                'yellowpages2',
                ['company' => $event->getCompany()]
            );
        }
    }
}
