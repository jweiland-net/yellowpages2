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
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CategoryRepository;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Helper\MailHelper;
use JWeiland\Yellowpages2\Service\CompanyAccessService;
use JWeiland\Yellowpages2\Service\ModerationLinkTokenService;
use JWeiland\Yellowpages2\Traits\PostProcessControllerActionTrait;
use JWeiland\Yellowpages2\Traits\PostProcessFluidVariablesTrait;
use JWeiland\Yellowpages2\Traits\PreProcessControllerActionTrait;
use JWeiland\Yellowpages2\Utility\CacheUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
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
        protected readonly ModerationLinkTokenService $moderationLinkTokenService,
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

    public function initializeConfirmActivationAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * Mail security scanners and link previews open the links of a mail
     * automatically. So the activation link of the mail shows this summary
     * of the company only. Its form activates the company via POST.
     */
    public function confirmActivationAction(#[IgnoreValidation] Company $company): ResponseInterface
    {
        $moderationToken = $this->getValidModerationToken((int)$company->getUid());
        if ($moderationToken === '' || !$company->getHidden()) {
            return $this->redirectToCompanyList(...$this->getActivationError($moderationToken));
        }

        $this->postProcessAndAssignFluidVariables([
            'company' => $company,
            'moderationToken' => $moderationToken,
        ]);

        return $this->htmlResponse();
    }

    public function initializeActivateAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * The company is activated via POST only. GET requests (e.g. activation
     * links of former mails) are redirected to the confirmation page.
     * The action gets the company UID only, so no property of the company is
     * mapped and validated. Activation changes the "hidden" state only.
     */
    public function activateAction(int $company): ResponseInterface
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->redirect('confirmActivation', null, null, [
                'company' => $company,
                'moderationToken' => $this->getValidModerationToken($company),
            ]);
        }

        $companyObject = $this->companyRepository->findHiddenObject($company);
        $moderationToken = $this->getValidModerationToken($company);
        if (!$companyObject instanceof Company || $moderationToken === '' || !$companyObject->getHidden()) {
            return $this->redirectToCompanyList(...$this->getActivationError($companyObject instanceof Company ? $moderationToken : ''));
        }

        $this->activateCompany($companyObject);

        return $this->redirectToCompanyList(
            (string)LocalizationUtility::translate('activation.success', ExtConf::EXT_KEY),
            ContextualFeedbackSeverity::OK,
        );
    }

    protected function activateCompany(Company $company): void
    {
        $company->setHidden(false);
        $this->companyRepository->update($company);

        $this->postProcessControllerAction($company);
        $this->postProcessAndAssignFluidVariables([
            'company' => $company,
        ]);

        $this->mailHelper->sendMail(
            $this->view->render(),
            (string)LocalizationUtility::translate('email.subject.activate', ExtConf::EXT_KEY),
        );
    }

    /**
     * The token of the signed mail link. An empty string, if it is missing
     * or invalid for the company. Owners of a company never get a token, so
     * they can not bypass the moderation.
     */
    protected function getValidModerationToken(int $companyUid): string
    {
        $moderationToken = $this->request->hasArgument('moderationToken')
            ? $this->request->getArgument('moderationToken')
            : '';

        return is_string($moderationToken)
            && $moderationToken !== ''
            && $this->moderationLinkTokenService->isValidToken($companyUid, $moderationToken)
            ? $moderationToken
            : '';
    }

    /**
     * Without a valid token the company can not be activated. With a valid
     * token the company is visible already, e.g. as the link was used before.
     *
     * @return array{0: string, 1: ContextualFeedbackSeverity}
     */
    protected function getActivationError(string $moderationToken): array
    {
        return $moderationToken === ''
            ? [(string)LocalizationUtility::translate('activation.invalidToken', ExtConf::EXT_KEY), ContextualFeedbackSeverity::ERROR]
            : [(string)LocalizationUtility::translate('activation.alreadyActivated', ExtConf::EXT_KEY), ContextualFeedbackSeverity::INFO];
    }

    /**
     * The list action of CompanyController belongs to another plugin
     * ("Directory") on another page (settings.pidOfListPage), so
     * ActionController::redirect() can not be used here. The message is
     * added to the flash messages of the Directory plugin, which renders them.
     */
    protected function redirectToCompanyList(
        string $message = '',
        ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::OK,
    ): ResponseInterface {
        if ($message !== '') {
            $this->getFlashMessageQueue(CompanyController::FLASH_MESSAGE_QUEUE_IDENTIFIER)->enqueue(
                GeneralUtility::makeInstance(FlashMessage::class, $message, '', $severity, true),
            );
        }

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
