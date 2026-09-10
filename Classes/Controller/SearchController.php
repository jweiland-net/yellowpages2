<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Controller;

use JWeiland\Yellowpages2\Domain\Repository\CategoryRepository;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Traits\InitializeActionTrait;
use JWeiland\Yellowpages2\Traits\PostProcessFluidVariablesTrait;
use JWeiland\Yellowpages2\Traits\PreProcessControllerActionTrait;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller to show the search form and its (uncached) search results
 */
class SearchController extends ActionController
{
    use InitializeActionTrait;
    use PostProcessFluidVariablesTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        protected readonly CompanyRepository $companyRepository,
        protected readonly CategoryRepository $categoryRepository,
    ) {}

    public function initializeShowAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function showAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function initializeListSearchResultsAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function listSearchResultsAction(string $search, int $category = 0): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'search' => $search,
            'category' => $category,
            'companies' => $this->companyRepository->searchCompanies($search, $category, $this->settings),
            'categories' => $this->categoryRepository->findRelated(),
        ]);

        return $this->htmlResponse();
    }
}
