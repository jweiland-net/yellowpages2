<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Service;

use TYPO3\CMS\Core\Crypto\HashService;

/**
 * Generates and validates the "moderationToken" request argument used to authorize the edit/activate
 * links sent by mail to the site's administrator. Access via these links must not depend on any
 * frontend user session, as the administrator is never logged in as the frontend user who submitted
 * the company.
 */
final readonly class ModerationLinkTokenService
{
    private const ADDITIONAL_SECRET = 'yellowpages2/company-moderation-link';

    public function __construct(
        private HashService $hashService,
    ) {}

    public function generateToken(int $companyUid): string
    {
        return $this->hashService->hmac((string)$companyUid, self::ADDITIONAL_SECRET);
    }

    public function isValidToken(int $companyUid, string $token): bool
    {
        return $this->hashService->validateHmac((string)$companyUid, self::ADDITIONAL_SECRET, $token);
    }
}
