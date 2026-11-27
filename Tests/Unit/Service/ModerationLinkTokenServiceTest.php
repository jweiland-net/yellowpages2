<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Service;

use JWeiland\Yellowpages2\Service\ModerationLinkTokenService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Crypto\HashAlgo;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ModerationLinkTokenServiceTest extends UnitTestCase
{
    private const ADDITIONAL_SECRET = 'yellowpages2/company-moderation-link';

    private HashService $hashService;

    private LoggerInterface&MockObject $logger;

    private ModerationLinkTokenService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'test-encryption-key';

        $this->hashService = new HashService();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->subject = new ModerationLinkTokenService($this->hashService, $this->logger);
    }

    #[Test]
    public function generateTokenCreatesSha3HmacOfCompanyUid(): void
    {
        $token = $this->subject->generateToken(12);

        self::assertSame($this->hashService->hmac('12', self::ADDITIONAL_SECRET, HashAlgo::SHA3_256), $token);
        self::assertSame(HashAlgo::SHA3_256->length(), strlen($token));
    }

    #[Test]
    public function isValidTokenAcceptsGeneratedTokenWithoutLogging(): void
    {
        $this->logger->expects($this->never())->method(self::anything());

        self::assertTrue($this->subject->isValidToken(12, $this->subject->generateToken(12)));
    }

    #[Test]
    public function isValidTokenRejectsTokenOfOtherCompany(): void
    {
        self::assertFalse($this->subject->isValidToken(13, $this->subject->generateToken(12)));
    }

    #[Test]
    public function isValidTokenRejectsInvalidAndEmptyTokens(): void
    {
        $this->logger->expects($this->never())->method(self::anything());

        self::assertFalse($this->subject->isValidToken(12, 'invalid'));
        self::assertFalse($this->subject->isValidToken(12, ''));
    }

    #[Test]
    public function isValidTokenAcceptsLegacySha1TokenAndLogsNotice(): void
    {
        $legacyToken = $this->hashService->hmac('12', self::ADDITIONAL_SECRET);
        $this->logger->expects($this->once())->method('notice')->with(
            self::stringContains('legacy SHA1 moderation token'),
            ['companyUid' => 12],
        );

        self::assertTrue($this->subject->isValidToken(12, $legacyToken));
    }

    #[Test]
    public function isValidTokenRejectsLegacyTokenOfOtherCompany(): void
    {
        $this->logger->expects($this->never())->method(self::anything());

        self::assertFalse($this->subject->isValidToken(13, $this->hashService->hmac('12', self::ADDITIONAL_SECRET)));
    }
}
