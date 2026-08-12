<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\EventListener;

use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Event\PreProcessControllerActionEvent;
use JWeiland\Yellowpages2\Helper\HiddenObjectHelper;
use TYPO3\CMS\Core\Attribute\AsEventListener;

/**
 * Access the request in an initialize action and try to make hidden objects available in origin action
 */
#[AsEventListener(
    identifier: 'yellowpages2/register-hidden-company',
)]
final class RegisterHiddenCompanyEventListener extends AbstractControllerEventListener
{
    protected array $allowedControllerActions = [
        'Company' => [
            'edit',
            'update',
            'activate',
        ],
        'Map' => [
            'edit',
            'update',
        ],
    ];

    public function __construct(
        private readonly HiddenObjectHelper $hiddenObjectHelper,
        private readonly CompanyRepository $companyRepository,
    ) {}

    public function __invoke(PreProcessControllerActionEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            $this->hiddenObjectHelper->registerHiddenObjectInExtbaseSession(
                $this->companyRepository,
                $event->getRequest(),
                'company',
            );
        }
    }
}
