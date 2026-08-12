<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\EventListener;

use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Event\PostProcessControllerActionEvent;
use JWeiland\Yellowpages2\Helper\PathSegmentHelper;
use JWeiland\Yellowpages2\Traits\IsValidEventListenerRequestTrait;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;

/**
 * Update path_segment of the company.
 * Only sets the property on the model. Persisting happens automatically afterward,
 * as CompanyController::createAction() adds the company to the CompanyRepository.
 */
#[AsEventListener(
    identifier: 'yellowpages2/update-company-path-segment',
)]
final readonly class UpdateCompanyPathSegmentEventListener
{
    use IsValidEventListenerRequestTrait;

    private const ALLOWED_CONTROLLER_ACTIONS = [
        'Company' => [
            'create',
        ],
    ];

    public function __construct(
        private PathSegmentHelper $pathSegmentHelper,
    ) {}

    /**
     * @throws SiteNotFoundException
     */
    public function __invoke(PostProcessControllerActionEvent $event): void
    {
        if ($this->isValidRequest($event) && $event->getCompany() instanceof Company) {
            $this->pathSegmentHelper->updatePathSegmentForCompany($event->getCompany());
        }
    }
}
