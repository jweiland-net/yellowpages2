<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Form;

use JWeiland\Yellowpages2\Service\ModerationLinkTokenService;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Crypto\HashAlgo;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Form\Domain\Runtime\FormState;
use TYPO3\CMS\Form\Security\HashScope;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Submits the map step of the company form in frontend and follows the
 * activation link of the moderation mail. The mail is written into a mbox
 * file. The map step needs the form element "Maps2PoiPosition" of EXT:maps2
 * 12.3.0, so the tests are skipped with older versions.
 */
class CompanyFormModerationMailTest extends FunctionalTestCase
{
    private const PLUGIN_NAMESPACE = 'tx_yellowpages2_management';

    private const MBOX_FILE = '/tmp/yellowpages2-functional-test.mbox';

    private const MANAGEMENT_PAGE = 2;

    private const FRONTEND_USER = 6;

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
        'MAIL' => [
            'transport' => 'mbox',
            'transport_mbox_file' => self::MBOX_FILE,
            'defaultMailFromAddress' => 'noreply@example.com',
            'defaultMailFromName' => 'Yellowpages2',
        ],
        'EXTENSIONS' => [
            'yellowpages2' => [
                'poiCollectionPid' => '11',
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

        if (version_compare(ExtensionManagementUtility::getExtensionVersion('maps2'), '12.3.0', '<')) {
            self::markTestSkipped('The form element "Maps2PoiPosition" of EXT:maps2 >= 12.3.0 is not available.');
        }

        @unlink(self::MBOX_FILE);
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/CompanyForm.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:yellowpages2/Tests/Functional/Fixtures/Frontend/Constants.typoscript'],
                'setup' => ['EXT:yellowpages2/Tests/Functional/Fixtures/Frontend/CompanyForm.typoscript'],
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

    protected function tearDown(): void
    {
        @unlink(self::MBOX_FILE);
        parent::tearDown();
    }

    #[Test]
    public function submitOfCompanyFormSendsMailWithLinksOfYellowpages2(): void
    {
        $this->submitMapPage();

        $company = $this->getNewCompany();
        $moderationToken = $this->get(ModerationLinkTokenService::class)->generateToken((int)$company['uid']);
        $mail = $this->getMail();

        self::assertSame(1, (int)$company['hidden']);
        self::assertSame(self::FRONTEND_USER, (int)$company['fe_user']);
        self::assertGreaterThan(0, (int)$company['tx_maps2_uid']);
        self::assertStringContainsString('To: Moderator <moderator@example.com>', $mail);
        self::assertStringContainsString(urlencode(self::PLUGIN_NAMESPACE . '[action]') . '=perform', $mail);
        self::assertStringContainsString(urlencode(self::PLUGIN_NAMESPACE . '[action]') . '=confirmActivation', $mail);
        self::assertStringContainsString(urlencode(self::PLUGIN_NAMESPACE . '[company]') . '=' . $company['uid'], $mail);
        self::assertStringContainsString(
            urlencode(self::PLUGIN_NAMESPACE . '[moderationToken]') . '=' . $moderationToken,
            $mail,
        );
        self::assertStringNotContainsString('tx_events2', $mail);
        self::assertStringNotContainsString('tx_clubdirectory', $mail);
    }

    /**
     * Links in the text part are copied by the moderator. They must not
     * contain HTML entities like "&amp;", else the parameters are broken.
     */
    #[Test]
    public function textPartOfMailContainsLinksWithoutHtmlEntities(): void
    {
        $this->submitMapPage();

        $textPart = $this->getTextPart();

        self::assertMatchesRegularExpression(
            '#https://\\S+' . preg_quote(urlencode(self::PLUGIN_NAMESPACE . '[action]'), '#') . '=confirmActivation&\\S+#',
            $textPart,
        );
        self::assertMatchesRegularExpression(
            '#https://\\S+' . preg_quote(urlencode(self::PLUGIN_NAMESPACE . '[action]'), '#') . '=perform&\\S+#',
            $textPart,
        );
        foreach (['&amp;', '&quot;', '&#039;', '&lt;', '&gt;'] as $htmlEntity) {
            self::assertStringNotContainsString($htmlEntity, $textPart);
        }
    }

    #[Test]
    public function activationLinkOfMailShowsConfirmButtonAndActivatesCompany(): void
    {
        $this->submitMapPage();
        $company = $this->getNewCompany();
        $moderationToken = $this->get(ModerationLinkTokenService::class)->generateToken((int)$company['uid']);

        $content = (string)$this->request('confirmActivation', [
            'company' => (string)$company['uid'],
            'moderationToken' => $moderationToken,
        ])->getBody();

        self::assertStringContainsString('Flower shop', $content);
        self::assertStringContainsString('name="' . self::PLUGIN_NAMESPACE . '[moderationToken]" value="' . $moderationToken . '"', $content);

        $this->request('activate', [], [
            self::PLUGIN_NAMESPACE => [
                'company' => (string)$company['uid'],
                'moderationToken' => $moderationToken,
            ],
        ]);

        self::assertSame(0, (int)$this->getNewCompany()['hidden']);
    }

    /**
     * The values of the first page are part of the signed form state, so the
     * second page with the map can be submitted without geocoding.
     */
    private function submitMapPage(): void
    {
        $formState = new FormState();
        $formState->setLastDisplayedPageIndex(1);
        // Since TYPO3 14 the honeypot name of the displayed page is part of the signed form state
        $formState->setHoneypotName(1, 'yellowpages2Honeypot');
        $valuesOfFirstPage = [
            'company' => 'Flower shop',
            'street' => 'Main street',
            'house-number' => '1',
            'zip' => '70794',
            'city' => 'Filderstadt',
            'district' => '2',
            'description' => 'Fresh flowers',
            'main-trade' => '4',
        ];
        foreach ($valuesOfFirstPage as $identifier => $value) {
            $formState->setFormValue($identifier, $value);
        }

        $this->request('perform', [], [
            self::PLUGIN_NAMESPACE => [
                'yellowpages2Company' => [
                    '__state' => $this->get(HashService::class)->appendHmac(
                        base64_encode(serialize($formState)),
                        HashScope::FormState->prefix(),
                        HashAlgo::SHA3_256,
                    ),
                    '__currentPage' => '2',
                    'position' => ['latitude' => '48.6861', 'longitude' => '9.2159'],
                ],
            ],
        ], self::FRONTEND_USER);
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

    /**
     * Returns the decoded text/plain part of the sent mail
     */
    private function getTextPart(): string
    {
        self::assertFileExists(self::MBOX_FILE, 'No mail was sent');
        $mail = str_replace("\r\n", "\n", (string)file_get_contents(self::MBOX_FILE));

        $found = preg_match('#\\nContent-Type: text/plain[^\\n]*\\n((?:[^\\n]+\\n)*)\\n(.*?)\\n--#s', $mail, $matches);
        self::assertSame(1, $found, 'The mail has no text part');

        return str_contains(strtolower($matches[1]), 'quoted-printable') ? quoted_printable_decode($matches[2]) : $matches[2];
    }

    /**
     * Returns the decoded text and HTML part of the sent mail
     */
    private function getMail(): string
    {
        self::assertFileExists(self::MBOX_FILE, 'No mail was sent');

        return quoted_printable_decode((string)file_get_contents(self::MBOX_FILE));
    }

    /**
     * @return array<string, mixed>
     */
    private function getNewCompany(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_yellowpages2_domain_model_company');
        $queryBuilder->getRestrictions()->removeAll();
        $company = $queryBuilder
            ->select('*')
            ->from('tx_yellowpages2_domain_model_company')
            ->where($queryBuilder->expr()->eq('company', $queryBuilder->createNamedParameter('Flower shop')))
            ->executeQuery()
            ->fetchAssociative();
        self::assertIsArray($company, 'The company was not stored');

        return $company;
    }
}
