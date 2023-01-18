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

/**
 * Access the request in an initialize action and try to make hidden objects available in origin action
 */
class RegisterHiddenCompanyEventListener extends AbstractControllerEventListener
{
    /**
     * @var HiddenObjectHelper
     */
    protected $hiddenObjectHelper;

    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

    protected $allowedControllerActions = [
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
        HiddenObjectHelper $hiddenObjectHelper,
        CompanyRepository $companyRepository
    ) {
        $this->hiddenObjectHelper = $hiddenObjectHelper;
        $this->companyRepository = $companyRepository;
    }

    public function __invoke(PreProcessControllerActionEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            $this->hiddenObjectHelper->registerHiddenObjectInExtbaseSession(
                $this->companyRepository,
                $event->getRequest(),
                'company'
            );
        }
    }
}
