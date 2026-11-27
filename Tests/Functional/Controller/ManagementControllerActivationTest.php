<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Controller;

use JWeiland\Yellowpages2\Service\ModerationLinkTokenService;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The activation link of the moderation mail shows a confirmation page. Its
 * form activates the company via POST. The result is a flash message of the
 * Directory plugin. Rejected requests are logged without personal data and
 * show a flash message in the Management plugin.
 */
final class ManagementControllerActivationTest extends FunctionalTestCase
{
    private const PLUGIN_NAMESPACE = 'tx_yellowpages2_management';

    private const MANAGEMENT_PAGE = 2;

    /**
     * Hidden company of Fixtures/CompanyForm.csv, owned by frontend user 6
     */
    private const HIDDEN_COMPANY = 2;

    private const VISIBLE_COMPANY = 1;

    private const OWNER = 6;

    private const VISIBLE_COMPANY_OWNER = 5;

    protected array $coreExtensionsToLoad = [
        'form',
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/maps2',
        'jweiland/yellowpages2',
    ];

    protected array $configurationToUseInTestInstance = [
        'LOG' => [
            'JWeiland' => [
                'Yellowpages2' => [
                    'writerConfiguration' => [
                        LogLevel::NOTICE => [
                            FileWriter::class => ['logFile' => 'typo3temp/var/log/yellowpages2.log'],
                        ],
                    ],
                ],
            ],
        ],
        'MAIL' => [
            'transport' => 'null',
        ],
        'EXTENSIONS' => [
            'yellowpages2' => [
                'emailFromAddress' => 'sender@example.com',
                'emailFromName' => 'Sender',
                'emailToAddress' => 'moderator@example.com',
                'emailToName' => 'Moderator',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/CompanyForm.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:yellowpages2/Tests/Functional/Fixtures/Frontend/Constants.typoscript'],
                'setup' => ['EXT:yellowpages2/Tests/Functional/Fixtures/Frontend/Management.typoscript'],
            ],
        );
        $this->get(SiteWriter::class)->write('yellowpages2', [
            'rootPageId' => 1,
            'base' => 'https://www.example.com/',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'English',
                    'locale' => 'en_US.UTF-8',
                    'base' => '/',
                ],
            ],
        ]);
    }

    #[Test]
    public function confirmActivationWithValidTokenShowsConfirmationForm(): void
    {
        $moderationToken = $this->generateToken(self::HIDDEN_COMPANY);

        $content = (string)$this->request('confirmActivation', [
            'company' => (string)self::HIDDEN_COMPANY,
            'moderationToken' => $moderationToken,
        ])->getBody();

        self::assertStringContainsString('Florist without position', $content);
        self::assertMatchesRegularExpression('/<input[^>]*type="submit"[^>]*value="Activate company"/', $content);
        self::assertStringContainsString('name="' . self::PLUGIN_NAMESPACE . '[company]" value="' . self::HIDDEN_COMPANY . '"', $content);
        self::assertStringContainsString('name="' . self::PLUGIN_NAMESPACE . '[moderationToken]" value="' . $moderationToken . '"', $content);
        self::assertTrue($this->isCompanyHidden(self::HIDDEN_COMPANY));
    }

    #[Test]
    public function confirmActivationWithInvalidTokenRedirectsWithError(): void
    {
        $content = (string)$this->request('confirmActivation', [
            'company' => (string)self::HIDDEN_COMPANY,
            'moderationToken' => 'invalid',
        ])->getBody();

        self::assertStringNotContainsString('Activate company', $content);
        self::assertStringContainsString('The company could not be activated', $this->getFlashMessages());
        self::assertTrue($this->isCompanyHidden(self::HIDDEN_COMPANY));
    }

    /**
     * Mail security scanners and link previews must not activate the company.
     * Extbase sends the redirect to the confirmation page with header() in
     * frontend, so it is not part of the response of the sub request.
     */
    #[Test]
    public function activateViaGetKeepsCompanyHidden(): void
    {
        $this->request('activate', [
            'company' => (string)self::HIDDEN_COMPANY,
            'moderationToken' => $this->generateToken(self::HIDDEN_COMPANY),
        ]);

        self::assertTrue($this->isCompanyHidden(self::HIDDEN_COMPANY));
        self::assertStringNotContainsString('The company was activated', $this->getFlashMessages());
    }

    #[Test]
    public function activateViaPostWithValidTokenActivatesCompanyWithMessage(): void
    {
        $this->postActivate(self::HIDDEN_COMPANY, $this->generateToken(self::HIDDEN_COMPANY));

        self::assertFalse($this->isCompanyHidden(self::HIDDEN_COMPANY));
        self::assertStringContainsString('The company was activated and is now visible in frontend.', $this->getFlashMessages());
    }

    #[Test]
    public function activateViaPostWithInvalidTokenKeepsCompanyHiddenWithError(): void
    {
        $this->postActivate(self::HIDDEN_COMPANY, 'invalid');

        self::assertTrue($this->isCompanyHidden(self::HIDDEN_COMPANY));
        self::assertStringContainsString('The company could not be activated', $this->getFlashMessages());
    }

    #[Test]
    public function activateViaPostWithTokenOfOtherCompanyKeepsCompanyHidden(): void
    {
        $this->postActivate(self::HIDDEN_COMPANY, $this->generateToken(self::VISIBLE_COMPANY));

        self::assertTrue($this->isCompanyHidden(self::HIDDEN_COMPANY));
        self::assertStringContainsString('The company could not be activated', $this->getFlashMessages());
    }

    #[Test]
    public function activateViaPostByOwnerWithoutTokenKeepsCompanyHidden(): void
    {
        $this->postActivate(self::HIDDEN_COMPANY, '', self::OWNER);

        self::assertTrue($this->isCompanyHidden(self::HIDDEN_COMPANY));
    }

    #[Test]
    public function activateViaPostOfActivatedCompanyShowsInfo(): void
    {
        $this->postActivate(self::VISIBLE_COMPANY, $this->generateToken(self::VISIBLE_COMPANY));

        self::assertFalse($this->isCompanyHidden(self::VISIBLE_COMPANY));
        self::assertStringContainsString('The company is activated already.', $this->getFlashMessages());
    }

    #[Test]
    public function confirmActivationWithInvalidTokenLogsWarningWithoutPersonalData(): void
    {
        $this->request('confirmActivation', [
            'company' => (string)self::HIDDEN_COMPANY,
            'moderationToken' => 'invalid',
        ]);

        $log = $this->getLog();
        self::assertStringContainsString('The activation of a company was rejected', $log);
        self::assertStringContainsString('"companyUid":' . self::HIDDEN_COMPANY, $log);
        self::assertStringContainsString('"feUserUid":0', $log);
        self::assertStringNotContainsString('moderator@example.com', $log);
    }

    #[Test]
    public function activateViaPostWithLegacySha1TokenActivatesCompanyAndLogsNotice(): void
    {
        $this->postActivate(self::HIDDEN_COMPANY, $this->generateLegacyToken(self::HIDDEN_COMPANY));

        self::assertFalse($this->isCompanyHidden(self::HIDDEN_COMPANY));
        self::assertStringContainsString('A legacy SHA1 moderation token was used', $this->getLog());
    }

    #[Test]
    public function performByForeignUserShowsFlashMessageAndLogsWarning(): void
    {
        $content = (string)$this->request('perform', ['company' => (string)self::VISIBLE_COMPANY], [], self::OWNER)->getBody();

        self::assertStringContainsString('You are not allowed to edit company records', $content);
        self::assertStringNotContainsString('<form', $content);
        $log = $this->getLog();
        self::assertStringContainsString('The company does not belong to the user', $log);
        self::assertStringContainsString('"companyUid":' . self::VISIBLE_COMPANY . ',"feUserUid":' . self::OWNER, $log);
    }

    #[Test]
    public function performWithInvalidModerationTokenShowsFlashMessageAndLogsWarning(): void
    {
        $content = (string)$this->request('perform', [
            'company' => (string)self::HIDDEN_COMPANY,
            'moderationToken' => 'invalid',
        ])->getBody();

        self::assertStringContainsString('The moderation link is invalid', $content);
        self::assertStringContainsString('The moderation token is invalid', $this->getLog());
    }

    #[Test]
    public function listMyCompaniesWithoutLoginShowsMessageAndNoCompany(): void
    {
        $content = (string)$this->request('listMyCompanies')->getBody();

        self::assertStringContainsString('Please log in to create or edit companies.', $content);
        self::assertStringNotContainsString('Bakery', $content);
        self::assertStringNotContainsString('Florist', $content);
    }

    #[Test]
    public function listMyCompaniesShowsCompaniesOfLoggedInUserOnly(): void
    {
        $content = (string)$this->request('listMyCompanies', [], [], self::VISIBLE_COMPANY_OWNER)->getBody();

        self::assertStringContainsString('Bakery M', $content);
        self::assertStringNotContainsString('Florist', $content);
        self::assertStringNotContainsString('Please log in', $content);
    }

    private function postActivate(int $companyUid, string $moderationToken, int $frontendUserUid = 0): ResponseInterface
    {
        return $this->request('activate', [], [
            self::PLUGIN_NAMESPACE => [
                'company' => (string)$companyUid,
                'moderationToken' => $moderationToken,
            ],
        ], $frontendUserUid);
    }

    /**
     * @param array<string, string> $pluginArguments Arguments of the query string
     * @param array<string, mixed> $parsedBody
     */
    private function request(
        string $actionName,
        array $pluginArguments = [],
        array $parsedBody = [],
        int $frontendUserUid = 0,
    ): ResponseInterface {
        $queryParameters = [
            'id' => self::MANAGEMENT_PAGE,
            self::PLUGIN_NAMESPACE . '[action]' => $actionName,
            self::PLUGIN_NAMESPACE . '[controller]' => 'Management',
        ];
        foreach ($pluginArguments as $argumentName => $value) {
            $queryParameters[self::PLUGIN_NAMESPACE . '[' . $argumentName . ']'] = $value;
        }
        $queryParameters['cHash'] = $this->get(CacheHashCalculator::class)
            ->generateForParameters(HttpUtility::buildQueryString($queryParameters));

        $request = (new InternalRequest('https://www.example.com/'))->withQueryParameters($queryParameters);
        if ($parsedBody !== []) {
            $request = $request->withMethod('POST')->withParsedBody($parsedBody);
            // The testing framework can not build a body of nested arrays
            $request->getBody()->write(http_build_query($parsedBody));
        }

        $context = new InternalRequestContext();
        if ($frontendUserUid > 0) {
            $context = $context->withFrontendUserId($frontendUserUid);
        }

        return $this->executeFrontendSubRequest($request, $context);
    }

    private function generateToken(int $companyUid): string
    {
        return $this->get(ModerationLinkTokenService::class)->generateToken($companyUid);
    }

    /**
     * Token of mails sent before version 11.0.0
     */
    private function generateLegacyToken(int $companyUid): string
    {
        return $this->get(HashService::class)->hmac((string)$companyUid, 'yellowpages2/company-moderation-link');
    }

    /**
     * The content of all log files of the test instance
     */
    private function getLog(): string
    {
        $log = '';
        foreach (glob($this->instancePath . '/typo3temp/var/log/*.log') ?: [] as $logFile) {
            $log .= (string)file_get_contents($logFile);
        }

        return $log;
    }

    /**
     * The flash messages of the Directory plugin are stored in the session
     */
    private function getFlashMessages(): string
    {
        $sessionData = $this->getConnectionPool()
            ->getConnectionForTable('fe_sessions')
            ->select(['ses_data'], 'fe_sessions')
            ->fetchFirstColumn();

        return implode('', array_map(strval(...), $sessionData));
    }

    private function isCompanyHidden(int $companyUid): bool
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_yellowpages2_domain_model_company');
        $queryBuilder->getRestrictions()->removeAll();

        return (int)$queryBuilder
            ->select('hidden')
            ->from('tx_yellowpages2_domain_model_company')
            ->where($queryBuilder->expr()->eq('uid', $companyUid))
            ->executeQuery()
            ->fetchOne() === 1;
    }
}
