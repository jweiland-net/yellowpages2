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
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Event\PostProcessControllerActionEvent;
use JWeiland\Yellowpages2\Helper\PathSegmentHelper;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Update path_segment of company.
 * Please check, if this EventListener was loaded before other redirecting EventListeners.
 */
class UpdateCompanyPathSegmentEventListener extends AbstractControllerEventListener
{
    protected PathSegmentHelper $pathSegmentHelper;

    protected CompanyRepository $companyRepository;

    protected PersistenceManagerInterface $persistenceManager;

    protected array $allowedControllerActions = [
        'Company' => [
            'create',
        ],
    ];

    public function __construct(
        PathSegmentHelper $pathSegmentHelper,
        CompanyRepository $companyRepository,
        PersistenceManagerInterface $persistenceManager,
    ) {
        $this->pathSegmentHelper = $pathSegmentHelper;
        $this->companyRepository = $companyRepository;
        $this->persistenceManager = $persistenceManager;
    }

    public function __invoke(PostProcessControllerActionEvent $event): void
    {
        if (
            $this->isValidRequest($event)
            && $event->getCompany() instanceof Company
        ) {
            $this->pathSegmentHelper->updatePathSegmentForCompany($event->getCompany());
            $this->companyRepository->update($event->getCompany());
            $this->persistenceManager->persistAll();
        }
    }
}
