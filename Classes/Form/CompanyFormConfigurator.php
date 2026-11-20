<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Form;

use JWeiland\Yellowpages2\Configuration\ExtConf;
use JWeiland\Yellowpages2\Service\CompanyAccessService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Routing\PageArguments;

/**
 * Configures the company form based on EXT:form for the current request:
 *
 * - The company to edit and the "moderationToken" are part of the URI of
 *   the form. They are kept for the form action, so the same company is
 *   checked and stored.
 * - The finisher "EmailToReceiver" gets the sender and the recipient of the
 *   extension settings, if they are not set in the form definition.
 * - The finisher "Redirect" redirects to the current page (the list of the
 *   own companies), if no "pageUid" is set.
 */
readonly class CompanyFormConfigurator
{
    private const EMAIL_FINISHER = 'EmailToReceiver';

    private const REDIRECT_FINISHER = 'Redirect';

    public function __construct(
        private CompanyAccessService $companyAccessService,
        private ExtConf $extConf,
    ) {}

    /**
     * @param array<string, mixed> $configuration Form definition
     * @return array<string, mixed>
     */
    public function configure(array $configuration, ?ServerRequestInterface $request): array
    {
        if ($request instanceof ServerRequestInterface) {
            $configuration = $this->addCompanyToFormAction($configuration, $request);
        }

        $finishers = is_array($configuration['finishers'] ?? null) ? $configuration['finishers'] : [];
        foreach ($finishers as $key => $finisher) {
            $finishers[$key] = match (is_array($finisher) ? ($finisher['identifier'] ?? '') : '') {
                self::EMAIL_FINISHER => $this->configureEmailFinisher($finisher),
                self::REDIRECT_FINISHER => $this->configureRedirectFinisher($finisher, $request),
                default => $finisher,
            };
        }
        $configuration['finishers'] = $finishers;

        return $configuration;
    }

    /**
     * @param array<string, mixed> $configuration
     * @return array<string, mixed>
     */
    private function addCompanyToFormAction(array $configuration, ServerRequestInterface $request): array
    {
        $parameters = array_filter([
            'company' => $this->companyAccessService->getCompanyUid($request),
            'moderationToken' => $this->companyAccessService->getModerationToken($request),
        ]);

        if ($parameters !== []) {
            $configuration['renderingOptions']['additionalParams'][CompanyAccessService::PLUGIN_NAMESPACE] = $parameters;
        }

        return $configuration;
    }

    /**
     * @param array<string, mixed> $finisher
     * @return array<string, mixed>
     */
    private function configureEmailFinisher(array $finisher): array
    {
        // The extension settings are read only, if the form definition does not set the option
        $defaults = [
            'senderAddress' => $this->extConf->getEmailFromAddress(...),
            'senderName' => $this->extConf->getEmailFromName(...),
            'recipients' => fn(): array => [$this->extConf->getEmailToAddress() => $this->extConf->getEmailToName()],
        ];

        $options = array_filter(is_array($finisher['options'] ?? null) ? $finisher['options'] : []);
        foreach ($defaults as $option => $getDefault) {
            $options[$option] ??= $getDefault();
        }
        $finisher['options'] = $options;

        return $finisher;
    }

    /**
     * @param array<string, mixed> $finisher
     * @return array<string, mixed>
     */
    private function configureRedirectFinisher(array $finisher, ?ServerRequestInterface $request): array
    {
        $pageArguments = $request?->getAttribute('routing');
        if ((int)($finisher['options']['pageUid'] ?? 0) === 0 && $pageArguments instanceof PageArguments) {
            $finisher['options']['pageUid'] = $pageArguments->getPageId();
        }

        return $finisher;
    }
}
