<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Form;

use JWeiland\Yellowpages2\Configuration\ExtConf;
use JWeiland\Yellowpages2\Form\CompanyFormConfigurator;
use JWeiland\Yellowpages2\Service\CompanyAccessService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
class CompanyFormConfiguratorTest extends UnitTestCase
{
    private MockObject $companyAccessService;

    private CompanyFormConfigurator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyAccessService = $this->createMock(CompanyAccessService::class);
        $this->subject = new CompanyFormConfigurator(
            $this->companyAccessService,
            new ExtConf(
                emailFromAddress: 'from@example.com',
                emailFromName: 'From',
                emailToAddress: 'moderator@example.com',
                emailToName: 'Moderator',
            ),
        );
    }

    #[Test]
    public function configureAddsCompanyAndModerationTokenToFormAction(): void
    {
        $this->companyAccessService->method('getCompanyUid')->willReturn(12);
        $this->companyAccessService->method('getModerationToken')->willReturn('token');

        $configuration = $this->subject->configure([], $this->buildRequest());

        self::assertSame(
            ['tx_yellowpages2_management' => ['company' => 12, 'moderationToken' => 'token']],
            $configuration['renderingOptions']['additionalParams'],
        );
    }

    #[Test]
    public function configureForNewCompanyKeepsFormAction(): void
    {
        $this->companyAccessService->method('getCompanyUid')->willReturn(0);
        $this->companyAccessService->method('getModerationToken')->willReturn('');

        self::assertArrayNotHasKey('renderingOptions', $this->subject->configure([], $this->buildRequest()));
    }

    #[Test]
    public function configureAddsSenderAndRecipientOfExtensionSettings(): void
    {
        $configuration = $this->subject->configure(
            ['finishers' => [2 => ['identifier' => 'EmailToReceiver', 'options' => ['subject' => 'Subject', 'senderName' => '']]]],
            null,
        );

        self::assertSame(
            [
                'senderAddress' => 'from@example.com',
                'senderName' => 'From',
                'recipients' => ['moderator@example.com' => 'Moderator'],
                'subject' => 'Subject',
            ],
            $configuration['finishers'][2]['options'],
        );
    }

    #[Test]
    public function configureKeepsSenderOfFormDefinition(): void
    {
        $configuration = $this->subject->configure(
            ['finishers' => [['identifier' => 'EmailToReceiver', 'options' => ['senderAddress' => 'own@example.com']]]],
            null,
        );

        self::assertSame('own@example.com', $configuration['finishers'][0]['options']['senderAddress']);
    }

    #[Test]
    public function configureRedirectsToCurrentPageWithoutPageUid(): void
    {
        $configuration = $this->subject->configure(
            ['finishers' => [4 => ['identifier' => 'Redirect', 'options' => ['pageUid' => '0']]]],
            $this->buildRequest(),
        );

        self::assertSame(7, $configuration['finishers'][4]['options']['pageUid']);
    }

    #[Test]
    public function configureKeepsPageUidOfRedirect(): void
    {
        $configuration = $this->subject->configure(
            ['finishers' => [4 => ['identifier' => 'Redirect', 'options' => ['pageUid' => '3']]]],
            $this->buildRequest(),
        );

        self::assertSame('3', $configuration['finishers'][4]['options']['pageUid']);
    }

    private function buildRequest(): ServerRequestInterface
    {
        return (new ServerRequest('https://example.com/'))->withAttribute('routing', new PageArguments(7, '0', []));
    }
}
