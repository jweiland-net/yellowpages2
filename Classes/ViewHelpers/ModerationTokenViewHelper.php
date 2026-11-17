<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\ViewHelpers;

use JWeiland\Yellowpages2\Service\ModerationLinkTokenService;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the "moderationToken" for the edit and activation links of a
 * company. It is needed in templates without a controller, which could
 * assign the token, e.g. the mail template of the EmailToReceiver finisher
 * of EXT:form.
 *
 * Example:
 * <f:variable name="moderationToken" value="{yp:moderationToken(company: company)}"/>
 */
final class ModerationTokenViewHelper extends AbstractViewHelper
{
    public function __construct(
        private readonly ModerationLinkTokenService $moderationLinkTokenService,
    ) {}

    public function initializeArguments(): void
    {
        $this->registerArgument('company', 'mixed', 'The company object or the UID of the company', true);
    }

    public function render(): string
    {
        $companyUid = $this->getCompanyUid($this->arguments['company']);

        return $companyUid > 0 ? $this->moderationLinkTokenService->generateToken($companyUid) : '';
    }

    private function getCompanyUid(mixed $company): int
    {
        if ($company instanceof AbstractDomainObject) {
            return (int)$company->getUid();
        }

        return is_numeric($company) ? (int)$company : 0;
    }
}
