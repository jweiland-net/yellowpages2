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
use JWeiland\Yellowpages2\Event\ControllerActionEventInterface;
use JWeiland\Yellowpages2\Event\PreProcessControllerActionEvent;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
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
#[AsEventListener('yellowpages2/restrictAccess')]
final class RestrictAccessEventListener
{

    protected const ALLOWED_CONTROLLER_ACTIONS = [
        'Company' => [
            'edit',
            'update',
        ],
        'Map' => [
            'update',
            'create',
        ],
    ];

    private ?ServerRequestInterface $request = null;

    public function __construct(
        private readonly FlashMessageService $flashMessageService,
        private readonly CompanyRepository $companyRepository,
        private readonly ExtensionService $extensionService,
    ) {}

    public function __invoke(PreProcessControllerActionEvent $controllerActionEvent): void
    {
        if (!$this->isValidRequest($controllerActionEvent)) {
            return;
        }

        $this->request = $controllerActionEvent->getRequest();

        if ($this->isAccessAllowed($controllerActionEvent)) {
            return;
        }

        $controllerActionEvent->setRequest(
            $this->request->withControllerActionName('error'),
        );

        $controllerActionEvent->setArguments(
            GeneralUtility::makeInstance(Arguments::class),
        );
    }

    private function isAccessAllowed(PreProcessControllerActionEvent $controllerActionEvent): bool
    {
        $request = $controllerActionEvent->getRequest();

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

    private function addFlashMessage($messageBody): void
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

    private function getFlashMessageQueue(?string $identifier = null): FlashMessageQueue
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

    protected function isValidRequest(ControllerActionEventInterface $event): bool
    {
        return
            array_key_exists($event->getControllerName(), self::ALLOWED_CONTROLLER_ACTIONS)
            && in_array(
                $event->getActionName(),
                self::ALLOWED_CONTROLLER_ACTIONS[$event->getControllerName()],
                true,
            );
    }
}
