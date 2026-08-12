<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\EventListener;

use JWeiland\Yellowpages2\Event\PostProcessFluidVariablesEvent;
use JWeiland\Yellowpages2\Pagination\CompanyPagination;
use JWeiland\Yellowpages2\Traits\IsValidEventListenerRequestTrait;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;

/**
 * Register paginator to paginate through the company records in frontend
 */
#[AsEventListener(
    identifier: 'yellowpages2/add-paginator',
)]
final readonly class AddPaginatorEventListener
{
    use IsValidEventListenerRequestTrait;

    private const ITEMS_PER_PAGE = 15;

    /**
     * Fluid variable name for paginated records
     */
    private const FLUID_VARIABLE_FOR_PAGINATED_RECORDS = 'companies';

    private const FALLBACK_PAGINATION_CLASS = CompanyPagination::class;

    protected const ALLOWED_CONTROLLER_ACTIONS = [
        'Company' => [
            'list',
            'search',
        ],
    ];

    public function __invoke(PostProcessFluidVariablesEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            $paginator = new QueryResultPaginator(
                $event->getFluidVariables()[self::FLUID_VARIABLE_FOR_PAGINATED_RECORDS],
                $this->getCurrentPage($event),
                $this->getItemsPerPage($event),
            );

            $event->addFluidVariable('actionName', $event->getActionName());
            $event->addFluidVariable('paginator', $paginator);
            $event->addFluidVariable(
                self::FLUID_VARIABLE_FOR_PAGINATED_RECORDS,
                $paginator->getPaginatedItems(),
            );
            $event->addFluidVariable('pagination', $this->getPagination($event, $paginator));
        }
    }

    private function getCurrentPage(PostProcessFluidVariablesEvent $controllerActionEvent): int
    {
        $currentPage = 1;
        if ($controllerActionEvent->getRequest()->hasArgument('currentPage')) {
            // $currentPage have to be positive and greater than 0
            // See: AbstractPaginator::setCurrentPageNumber()
            $currentPage = MathUtility::forceIntegerInRange(
                (int)$controllerActionEvent->getRequest()->getArgument('currentPage'),
                1,
            );
        }

        return $currentPage;
    }

    private function getItemsPerPage(PostProcessFluidVariablesEvent $event): int
    {
        return (int)($event->getSettings()['pageBrowser']['itemsPerPage'] ?? self::ITEMS_PER_PAGE);
    }

    private function getPagination(
        PostProcessFluidVariablesEvent $event,
        PaginatorInterface $paginator,
    ): PaginationInterface {
        $paginationClass = $event->getSettings()['pageBrowser']['class'] ?? self::FALLBACK_PAGINATION_CLASS;

        if (!class_exists($paginationClass)) {
            $paginationClass = self::FALLBACK_PAGINATION_CLASS;
        }

        if (!is_subclass_of($paginationClass, PaginationInterface::class)) {
            $paginationClass = self::FALLBACK_PAGINATION_CLASS;
        }

        return GeneralUtility::makeInstance($paginationClass, $paginator);
    }
}
