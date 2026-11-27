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
use JWeiland\Yellowpages2\Service\ModerationLinkTokenService;
use JWeiland\Yellowpages2\Service\PluginSettingsService;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
final class CompanyFormConfiguratorTest extends UnitTestCase
{
    private CompanyAccessService $companyAccessService;

    private CompanyFormConfigurator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyAccessService = new CompanyAccessService(
            new Context(),
            new ConnectionPool(),
            new ModerationLinkTokenService(new HashService(), new NullLogger()),
            new PluginSettingsService(new TypoScriptService(), new NullLogger()),
        );
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
        $request = $this->buildRequest(['company' => '12', 'moderationToken' => 'token']);

        $configuration = $this->subject->configure([], $request);

        self::assertSame(
            ['tx_yellowpages2_management' => ['company' => 12, 'moderationToken' => 'token']],
            $configuration['renderingOptions']['additionalParams'],
        );
    }

    #[Test]
    public function configureForNewCompanyKeepsFormAction(): void
    {
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
                'subject' => 'Subject',
                'senderAddress' => 'from@example.com',
                'senderName' => 'From',
                'recipients' => ['moderator@example.com' => 'Moderator'],
            ],
            $configuration['finishers'][2]['options'],
        );
    }

    /**
     * Without sender address in extension settings and install tool,
     * ExtConf throws an exception. It must not be called.
     */
    #[Test]
    public function configureWithSenderAndRecipientOfFormDefinitionDoesNotReadExtensionSettings(): void
    {
        $subject = new CompanyFormConfigurator($this->companyAccessService, new ExtConf());
        $options = [
            'senderAddress' => 'own@example.com',
            'senderName' => 'Own',
            'recipients' => ['team@example.com' => 'Team'],
        ];

        $configuration = $subject->configure(['finishers' => [['identifier' => 'EmailToReceiver', 'options' => $options]]], null);

        self::assertSame($options, $configuration['finishers'][0]['options']);
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

    /**
     * @param array<string, string> $pluginQueryParameters Query parameters of tx_yellowpages2_management
     */
    private function buildRequest(array $pluginQueryParameters = []): ServerRequestInterface
    {
        return (new ServerRequest('https://example.com/'))
            ->withQueryParams(['tx_yellowpages2_management' => $pluginQueryParameters])
            ->withAttribute('routing', new PageArguments(7, '0', []));
    }
}
