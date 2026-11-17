<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Service;

use JWeiland\Yellowpages2\Service\CompanyAccessService;
use JWeiland\Yellowpages2\Tests\Functional\Traits\CompanyFormRequestTrait;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class CompanyAccessServiceTest extends FunctionalTestCase
{
    use CompanyFormRequestTrait;

    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/maps2',
        'jweiland/yellowpages2',
    ];

    private CompanyAccessService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/CompanyForm.csv');
        $this->subject = $this->get(CompanyAccessService::class);
    }

    #[Test]
    public function getCompanyUidReadsQuery(): void
    {
        self::assertSame(3, $this->subject->getCompanyUid($this->buildFormRequest(['company' => '3'])));
        self::assertSame(0, $this->subject->getCompanyUid($this->buildFormRequest(['company' => 'abc'])));
        self::assertSame(0, $this->subject->getCompanyUid($this->buildFormRequest()));
    }

    #[Test]
    public function getDenialReasonWithoutUserGroupReturnsReason(): void
    {
        $this->loginFrontendUser(5);

        self::assertSame('userGroupNotConfigured', $this->subject->getDenialReason($this->buildFormRequest([], '0')));
    }

    #[Test]
    public function getDenialReasonWithoutLoginReturnsReason(): void
    {
        self::assertSame('pluginNeedsLogin', $this->subject->getDenialReason($this->buildFormRequest()));
    }

    #[Test]
    public function getDenialReasonForUserOfOtherGroupReturnsReason(): void
    {
        $this->loginFrontendUser(5, 2);

        self::assertSame('notAllowedToCreate', $this->subject->getDenialReason($this->buildFormRequest()));
    }

    #[Test]
    public function getDenialReasonForNewCompanyOfUserInGroupAllowsAccess(): void
    {
        $this->loginFrontendUser(6);

        self::assertSame('', $this->subject->getDenialReason($this->buildFormRequest()));
    }

    #[Test]
    public function getDenialReasonForOwnCompanyAllowsAccess(): void
    {
        $this->loginFrontendUser(5);

        self::assertSame('', $this->subject->getDenialReason($this->buildFormRequest(['company' => '1'])));
    }

    #[Test]
    public function getDenialReasonForForeignCompanyReturnsReason(): void
    {
        $this->loginFrontendUser(6);

        self::assertSame('unauthorizedCompanyUser', $this->subject->getDenialReason($this->buildFormRequest(['company' => '1'])));
    }

    #[Test]
    public function getDenialReasonWithModerationTokenAllowsAccessWithoutLogin(): void
    {
        self::assertSame('', $this->subject->getDenialReason($this->buildModerationRequest(2)));
    }

    #[Test]
    public function getDenialReasonWithTokenOfOtherCompanyReturnsReason(): void
    {
        $request = $this->buildModerationRequest(2)->withQueryParams([
            'tx_yellowpages2_management' => [
                'company' => '1',
                'moderationToken' => $this->buildModerationRequest(2)->getQueryParams()['tx_yellowpages2_management']['moderationToken'],
            ],
        ]);

        self::assertSame('pluginNeedsLogin', $this->subject->getDenialReason($request));
    }

    #[Test]
    public function getDenialReasonWithModerationTokenOfDeletedCompanyReturnsReason(): void
    {
        self::assertSame('unauthorizedCompanyUser', $this->subject->getDenialReason($this->buildModerationRequest(99)));
    }
}
