<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Form;

use JWeiland\Yellowpages2\Form\RichTextEditorOptions;
use JWeiland\Yellowpages2\Sanitizer\RichTextSanitizer;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * The assets of the rich text editor are loaded by a Fluid partial, the
 * folder of the translations of CKEditor is configurable
 */
class RichTextEditorOptionsTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
        'rte_ckeditor',
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/maps2',
        'jweiland/yellowpages2',
    ];

    #[Test]
    public function loadAssetsPartialAddsModuleAndStyleSheets(): void
    {
        $context = $this->get(RenderingContextFactory::class)->create();
        $context->getTemplatePaths()->setPartialRootPaths(['EXT:yellowpages2/Resources/Private/FormFramework/Partials/']);
        $context->getTemplatePaths()->setTemplateSource('<f:render partial="RichTextEditor/LoadAssets"/>');
        (new TemplateView($context))->render();

        $assetCollector = $this->get(AssetCollector::class);
        self::assertContains('@jweiland/yellowpages2/rich-text-editor.js', $assetCollector->getJavaScriptModules());
        self::assertSame(
            'EXT:rte_ckeditor/Resources/Public/Css/editor.css',
            $assetCollector->getStyleSheets()['yellowpages2-ckeditor']['source'],
        );
        self::assertSame(
            'EXT:yellowpages2/Resources/Public/Css/RichTextEditor.css',
            $assetCollector->getStyleSheets()['yellowpages2-rich-text-editor']['source'],
        );
    }

    #[Test]
    public function getEditorOptionsReturnsTranslationOfConfiguredFolder(): void
    {
        $options = $this->getEditorOptions('de_DE.UTF-8', 'EXT:rte_ckeditor/Resources/Public/Contrib/translations/');

        self::assertSame('de', $options['language']);
        self::assertStringEndsWith('rte_ckeditor/Resources/Public/Contrib/translations/de.js', $options['translationUrl']);
    }

    #[Test]
    public function getEditorOptionsWithoutTranslationInFolderReturnsEmptyUrl(): void
    {
        self::assertSame('', $this->getEditorOptions('de_DE.UTF-8', 'EXT:yellowpages2/Resources/Public/JavaScript/')['translationUrl']);
        self::assertSame('', $this->getEditorOptions('de_DE.UTF-8', '')['translationUrl']);
    }

    #[Test]
    public function getEditorOptionsForEnglishReturnsEmptyUrl(): void
    {
        self::assertSame('', $this->getEditorOptions('en_US.UTF-8', 'EXT:rte_ckeditor/Resources/Public/Contrib/translations/')['translationUrl']);
    }

    /**
     * @return array<string, mixed>
     */
    private function getEditorOptions(string $locale, string $translationsPath): array
    {
        $request = (new ServerRequest('https://example.com/', 'GET', null, [], ['HTTP_HOST' => 'example.com', 'HTTPS' => 'on']))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('language', new SiteLanguage(0, $locale, new Uri('/'), []));
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));

        return $this->get(RichTextEditorOptions::class)->getEditorOptions(
            $request,
            $this->get(RichTextSanitizer::class)->getConfiguration(),
            $translationsPath,
        );
    }
}
