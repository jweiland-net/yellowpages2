<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Controller;

use JWeiland\Yellowpages2\Configuration\ExtConf;
use JWeiland\Yellowpages2\Domain\Repository\CategoryRepository;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Helper\MailHelper;
use JWeiland\Yellowpages2\Service\CompanyAccessService;
use JWeiland\Yellowpages2\Traits\PostProcessControllerActionTrait;
use JWeiland\Yellowpages2\Traits\PostProcessFluidVariablesTrait;
use JWeiland\Yellowpages2\Traits\PreProcessControllerActionTrait;
use JWeiland\Yellowpages2\Utility\CacheUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to let frontend users list their own companies, to render the
 * company form based on EXT:form to create and edit companies and to let
 * moderators activate companies created in frontend.
 */
class ManagementController extends ActionController
{
    use PostProcessFluidVariablesTrait;
    use PostProcessControllerActionTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        protected readonly Context $context,
        protected readonly CompanyRepository $companyRepository,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly MailHelper $mailHelper,
        protected readonly CompanyAccessService $companyAccessService,
    ) {}

    public function initializeListMyCompaniesAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     */
    public function listMyCompaniesAction(): ResponseInterface
    {
        $user = $this->context->getAspect('frontend.user')->get('id');
        $companies = $this->companyRepository->findByFeUser((int)$user);
        $this->postProcessAndAssignFluidVariables([
            'companies' => $companies,
            'categories' => $this->categoryRepository->findRelated(),
        ]);

        CacheUtility::addPageCacheTagsByQuery($companies->getQuery(), $this->request);

        return $this->htmlResponse();
    }

    public function initializePerformAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * Renders the company form based on EXT:form to create and edit
     * companies. The form reads the company to edit from the query on its
     * own, see CompanyAccessService::getCompanyUid().
     */
    public function performAction(): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'denialReason' => $this->companyAccessService->getDenialReason($this->request),
        ]);

        return $this->htmlResponse();
    }

    public function initializeActivateAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * @throws \Exception
     */
    public function activateAction(int $company): ResponseInterface
    {
        $companyObject = $this->companyRepository->findByIdentifier($company);
        $companyObject->setHidden(false);

        $this->companyRepository->update($companyObject);

        $this->postProcessControllerAction($companyObject);

        $this->postProcessAndAssignFluidVariables([
            'company' => $companyObject,
        ]);

        $this->mailHelper->sendMail(
            $this->view->render(),
            LocalizationUtility::translate('email.subject.activate', ExtConf::EXT_KEY),
        );

        return $this->redirectToCompanyList();
    }

    /**
     * The list action of CompanyController belongs to another plugin
     * ("Directory") on another page (settings.pidOfListPage), so
     * ActionController::redirect() can not be used here.
     */
    protected function redirectToCompanyList(): ResponseInterface
    {
        $this->uriBuilder->reset();

        $pidOfListPage = (int)($this->settings['pidOfListPage'] ?? 0);
        if ($pidOfListPage > 0) {
            $this->uriBuilder->setTargetPageUid($pidOfListPage);
        }

        return $this->redirectToUri(
            $this->uriBuilder->uriFor('list', [], 'Company', ExtConf::EXT_KEY, 'Directory'),
        );
    }
}
