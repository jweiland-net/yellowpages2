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
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
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
        protected readonly CompanyRepository $companyRepository,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly MailHelper $mailHelper,
        protected readonly CompanyAccessService $companyAccessService,
        protected readonly ModerationLinkTokenService $moderationLinkTokenService,
        protected readonly LoggerInterface $logger,
    ) {}

    public function initializeListMyCompaniesAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * Only logged in users of the configured user group get their companies. Else the
     * page shows a message and no company, as an anonymous visitor would match companies without owner.
     */
    public function listMyCompaniesAction(): ResponseInterface
    {
        $denialReason = $this->companyAccessService->getFrontendUserDenialReason($this->request);
        if ($denialReason !== '') {
            $this->addFlashMessageByKey($denialReason, ContextualFeedbackSeverity::WARNING);
            $this->postProcessAndAssignFluidVariables([
                'denialReason' => $denialReason,
                'companies' => [],
            ]);

            return $this->htmlResponse();
        }

        $companies = $this->companyRepository->findByFeUser($this->companyAccessService->getFrontendUserUid());
        $this->postProcessAndAssignFluidVariables([
            'denialReason' => '',
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
        $denialReason = $this->companyAccessService->getDenialReason($this->request);
        if ($denialReason !== '') {
            $this->rejectAccess($denialReason);
        }

        $this->postProcessAndAssignFluidVariables([
            'denialReason' => $denialReason,
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
            $this->addActivationError((int)$company->getUid(), $moderationToken);

            return $this->redirectToCompanyList();
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
            $this->addActivationError($company, $companyObject instanceof Company ? $moderationToken : '');

            return $this->redirectToCompanyList();
        }

        $isMailSent = $this->activateCompany($companyObject);

        $this->addDirectoryFlashMessage('activation.success', ContextualFeedbackSeverity::OK);
        if (!$isMailSent) {
            $this->addDirectoryFlashMessage('activation.mailFailed', ContextualFeedbackSeverity::WARNING);
        }

        return $this->redirectToCompanyList();
    }

    /**
     * Returns false, if the mail to the moderator could not be sent. The company is activated anyway.
     */
    protected function activateCompany(Company $company): bool
    {
        $company->setHidden(false);
        $this->companyRepository->update($company);

        $this->postProcessControllerAction($company);
        $this->postProcessAndAssignFluidVariables([
            'company' => $company,
        ]);

        return $this->mailHelper->sendMail(
            $this->view->render(),
            $this->translate('email.subject.activate'),
            ['companyUid' => (int)$company->getUid(), 'feUserUid' => $this->companyAccessService->getFrontendUserUid()],
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
     */
    protected function addActivationError(int $companyUid, string $moderationToken): void
    {
        if ($moderationToken !== '') {
            $this->addDirectoryFlashMessage('activation.alreadyActivated', ContextualFeedbackSeverity::INFO);

            return;
        }

        $this->logger->warning(
            'The activation of a company was rejected: The moderation token is missing or invalid.',
            ['companyUid' => $companyUid, 'feUserUid' => $this->companyAccessService->getFrontendUserUid()],
        );
        $this->addDirectoryFlashMessage('activation.invalidToken', ContextualFeedbackSeverity::ERROR);
    }

    /**
     * Shows the reason as flash message. Rejected moderation tokens and foreign companies are
     * logged, as they may be attempts to access companies of other users.
     */
    protected function rejectAccess(string $denialReason): void
    {
        $context = [
            'companyUid' => $this->companyAccessService->getCompanyUid($this->request),
            'feUserUid' => $this->companyAccessService->getFrontendUserUid(),
        ];

        if ($this->companyAccessService->hasInvalidModerationToken($this->request)) {
            $this->logger->warning('Access to a company was rejected: The moderation token is invalid.', $context);
            $this->addFlashMessageByKey('moderation.invalidToken', ContextualFeedbackSeverity::ERROR);

            return;
        }

        if ($denialReason === CompanyAccessService::REASON_UNAUTHORIZED) {
            $this->logger->warning('Access to a company was rejected: The company does not belong to the user.', $context);
        }

        $this->addFlashMessageByKey(
            $denialReason,
            $denialReason === CompanyAccessService::REASON_UNAUTHORIZED
                ? ContextualFeedbackSeverity::ERROR
                : ContextualFeedbackSeverity::WARNING,
        );
    }

    /**
     * Adds a translated message to the flash messages of this plugin. They are rendered in
     * the same request, so they are not stored in the session of anonymous visitors.
     */
    protected function addFlashMessageByKey(string $labelKey, ContextualFeedbackSeverity $severity): void
    {
        $this->addFlashMessage($this->translate($labelKey), '', $severity, false);
    }

    /**
     * The list action of CompanyController belongs to another plugin
     * ("Directory") on another page (settings.pidOfListPage), so
     * ActionController::redirect() can not be used here. The messages are
     * added to the flash messages of the Directory plugin, which renders them.
     * They are stored in the session as the response is a redirect.
     */
    protected function addDirectoryFlashMessage(string $labelKey, ContextualFeedbackSeverity $severity): void
    {
        $this->getFlashMessageQueue(CompanyController::FLASH_MESSAGE_QUEUE_IDENTIFIER)->enqueue(
            new FlashMessage($this->translate($labelKey), '', $severity, true),
        );
    }

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

    protected function translate(string $labelKey): string
    {
        return (string)LocalizationUtility::translate($labelKey, ExtConf::EXT_KEY, request: $this->request);
    }
}
