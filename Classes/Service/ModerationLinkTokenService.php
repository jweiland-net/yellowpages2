<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Service;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Crypto\HashAlgo;
use TYPO3\CMS\Core\Crypto\HashService;

/**
 * Generates and validates the "moderationToken" request argument used to authorize the edit/activate
 * links sent by mail to the site's administrator. Access via these links must not depend on any
 * frontend user session, as the administrator is never logged in as the frontend user who submitted
 * the company.
 *
 * Tokens are HMACs with SHA3-256. Tokens of mails sent before version 11.0.0 are HMACs with SHA1. They
 * are still accepted during version 11.x, but a notice is logged. They will be dropped in version 12.
 */
final readonly class ModerationLinkTokenService
{
    private const ADDITIONAL_SECRET = 'yellowpages2/company-moderation-link';

    public function __construct(
        private HashService $hashService,
        private LoggerInterface $logger,
    ) {}

    public function generateToken(int $companyUid): string
    {
        return $this->hashService->hmac((string)$companyUid, self::ADDITIONAL_SECRET, HashAlgo::SHA3_256);
    }

    public function isValidToken(int $companyUid, string $token): bool
    {
        if ($this->hashService->validateHmac((string)$companyUid, self::ADDITIONAL_SECRET, $token, HashAlgo::SHA3_256)) {
            return true;
        }

        if (!$this->hashService->validateHmac((string)$companyUid, self::ADDITIONAL_SECRET, $token, HashAlgo::SHA1)) {
            return false;
        }

        $this->logger->notice(
            'A legacy SHA1 moderation token was used. Legacy tokens will be dropped in version 12.',
            ['companyUid' => $companyUid],
        );

        return true;
    }
}
