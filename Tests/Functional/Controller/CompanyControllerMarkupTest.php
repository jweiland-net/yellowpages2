<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Controller;

use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The templates of the Directory plugin render the markup of Bootstrap 5
 */
final class CompanyControllerMarkupTest extends FunctionalTestCase
{
    private const PLUGIN_NAMESPACE = 'tx_yellowpages2_directory';

    private const DIRECTORY_PAGE = 2;

    protected array $coreExtensionsToLoad = [
        'form',
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/maps2',
        'jweiland/yellowpages2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/CompanyForm.csv');
        // A second visible company. With one company per page the pagination is rendered.
        $this->getConnectionPool()->getConnectionForTable('tx_yellowpages2_domain_model_company')->update(
            'tx_yellowpages2_domain_model_company',
            ['hidden' => 0],
            ['uid' => 2],
        );
        $this->getConnectionPool()->getConnectionForTable('tx_yellowpages2_domain_model_company')->update(
            'tx_yellowpages2_domain_model_company',
            ['telephone' => '0711 123456'],
            ['uid' => 1],
        );
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:yellowpages2/Tests/Functional/Fixtures/Frontend/Constants.typoscript'],
                'setup' => ['EXT:yellowpages2/Tests/Functional/Fixtures/Frontend/Directory.typoscript'],
            ],
        );
        $this->get(SiteWriter::class)->write('yellowpages2', [
            'rootPageId' => 1,
            'base' => 'https://www.example.com/',
            // Site settings of EXT:glossary2 are needed to render the A-Z list
            'dependencies' => ['jweiland/glossary2'],
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
    public function listRendersSearchFormWithBootstrapClasses(): void
    {
        $content = $this->request('list');

        self::assertMatchesRegularExpression('/<select[^>]*class="form-select"/', $content);
        self::assertMatchesRegularExpression('/<input[^>]*class="form-control"/', $content);
        self::assertMatchesRegularExpression('/<input[^>]*type="submit"[^>]*class="btn btn-primary"|<input[^>]*class="btn btn-primary"[^>]*type="submit"/', $content);
        self::assertStringContainsString('<label for="yellowpages2-search-term" class="form-label">', $content);
    }

    #[Test]
    public function listRendersPaginationWithBootstrapClasses(): void
    {
        $content = $this->request('list');

        self::assertStringContainsString('<nav aria-label="Pagination">', $content);
        self::assertStringContainsString('<ul class="pagination">', $content);
        self::assertStringContainsString('<li class="page-item next">', $content);
        self::assertMatchesRegularExpression('/<a[^>]*class="page-link"/', $content);
        self::assertStringNotContainsString('f3-widget-paginator', $content);
    }

    #[Test]
    public function showRendersPropertiesWithoutLegacyClasses(): void
    {
        $content = $this->request('show', ['company' => '1']);

        self::assertStringContainsString('Bakery Müller', $content);
        self::assertStringContainsString('<div class="float-start me-3 mb-3">', $content);
        self::assertStringContainsString('Telephone: 0711 123456', $content);
        foreach (['icon-', 'grid_1', 'floatleft', 'floatright', 'thumbnail grid'] as $legacyClass) {
            self::assertStringNotContainsString($legacyClass, $content);
        }
    }

    #[Test]
    public function showOfUnknownCompanyRespondsWithNotFound(): void
    {
        $response = $this->requestResponse('show', ['company' => '999']);

        self::assertSame(404, $response->getStatusCode());
    }

    #[Test]
    public function showOfHiddenCompanyRespondsWithNotFound(): void
    {
        $this->getConnectionPool()->getConnectionForTable('tx_yellowpages2_domain_model_company')->update(
            'tx_yellowpages2_domain_model_company',
            ['hidden' => 1],
            ['uid' => 2],
        );

        self::assertSame(404, $this->requestResponse('show', ['company' => '2'])->getStatusCode());
    }

    /**
     * @param array<string, string> $pluginArguments
     */
    private function request(string $actionName, array $pluginArguments = []): string
    {
        return (string)$this->requestResponse($actionName, $pluginArguments)->getBody();
    }

    /**
     * @param array<string, string> $pluginArguments
     */
    private function requestResponse(string $actionName, array $pluginArguments = []): ResponseInterface
    {
        $queryParameters = [
            'id' => self::DIRECTORY_PAGE,
            self::PLUGIN_NAMESPACE . '[action]' => $actionName,
            self::PLUGIN_NAMESPACE . '[controller]' => 'Company',
        ];
        foreach ($pluginArguments as $argumentName => $value) {
            $queryParameters[self::PLUGIN_NAMESPACE . '[' . $argumentName . ']'] = $value;
        }
        $queryParameters['cHash'] = $this->get(CacheHashCalculator::class)
            ->generateForParameters(HttpUtility::buildQueryString($queryParameters));

        return $this->executeFrontendSubRequest(
            (new InternalRequest('https://www.example.com/'))->withQueryParameters($queryParameters),
        );
    }
}
