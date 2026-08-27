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
use JWeiland\Yellowpages2\Event\PreProcessControllerActionEvent;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Restrict access to certain controller actions if logged-in user tries to access other user's records.
 */
class RestrictAccessEventListener extends AbstractControllerEventListener
{
    protected FlashMessageService $flashMessageService;

    protected CompanyRepository $companyRepository;

    protected ExtensionService $extensionService;

    protected ?ServerRequestInterface $request = null;

    protected array $allowedControllerActions = [
        'Company' => [
            'edit',
            'update',
        ],
        'Map' => [
            'update',
            'create',
        ],
    ];

    public function __construct(
        FlashMessageService $flashMessageService,
        CompanyRepository $companyRepository,
        ExtensionService $extensionService,
    ) {
        $this->flashMessageService = $flashMessageService;
        $this->companyRepository = $companyRepository;
        $this->extensionService = $extensionService;
    }

    public function __invoke(PreProcessControllerActionEvent $event): void
    {
        if (!$this->isValidRequest($event)) {
            return;
        }

        $this->request = $event->getRequest();

        if ($this->isAccessAllowed($event)) {
            return;
        }

        $event->setRequest(
            $this->request->withControllerActionName('error'),
        );

        $event->setArguments(
            GeneralUtility::makeInstance(Arguments::class),
        );
    }

    protected function isAccessAllowed(PreProcessControllerActionEvent $event): bool
    {
        $request = $event->getRequest();

        if (!$request->hasArgument('company')) {
            return true;
        }
        $companyArgument = $request->getArgument('company');

        // Extract the UID: use __identity if it's an array, otherwise cast directly
        $companyUid = is_array($companyArgument)
            ? (int)($companyArgument['__identity'] ?? 0)
            : (int)$companyArgument;

        if (
            $companyUid > 0
            && ($company = $this->companyRepository->findHiddenObject($companyUid))
            && $company instanceof Company
            && $company->getHasValidUser() === false
        ) {
            $this->addFlashMessage(LocalizationUtility::translate('unauthorizedCompanyUser', 'yellowpages2'));

            return false;
        }

        return true;
    }

    protected function addFlashMessage($messageBody): void
    {
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            (string)$messageBody,
            '',
            ContextualFeedbackSeverity::ERROR,
            true,
        );

        $this->getFlashMessageQueue()->enqueue($flashMessage);
    }

    protected function getFlashMessageQueue(?string $identifier = null): FlashMessageQueue
    {
        if ($identifier === null) {
            $pluginNamespace = $this->extensionService->getPluginNamespace(
                $this->request->getControllerExtensionName(),
                $this->request->getPluginName(),
            );
            $identifier = 'extbase.flashmessages.' . $pluginNamespace;
        }

        return $this->flashMessageService->getMessageQueueByIdentifier($identifier);
    }
}
