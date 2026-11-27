<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Form;

use JWeiland\Yellowpages2\Sanitizer\RichTextSanitizerConfiguration;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\Publishing\UriGenerationOptions;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Builds the options of the CKEditor 5 of a rich text element, which are
 * added as JSON to the textarea. The JavaScript module and the style sheets
 * are loaded by the partial RichTextEditor/LoadAssets.html.
 */
readonly class RichTextEditorOptions
{
    public function __construct(
        private SystemResourceFactory $systemResourceFactory,
        private SystemResourcePublisherInterface $resourcePublisher,
    ) {}

    /**
     * The headings and toolbar items of the editor are based on the allowed tags.
     *
     * @param string $translationsPath Folder of the translations of CKEditor, e.g. "EXT:rte_ckeditor/Resources/Public/Contrib/translations/"
     * @return array<string, mixed>
     */
    public function getEditorOptions(
        ServerRequestInterface $request,
        RichTextSanitizerConfiguration $configuration,
        string $translationsPath,
    ): array {
        $languageCode = $this->getLanguageCode($request);

        return [
            'language' => $languageCode,
            'allowedTags' => $configuration->allowedTags,
            'translationUrl' => $this->getTranslationUrl($languageCode, $translationsPath, $request),
            'labels' => [
                'openInNewTab' => LocalizationUtility::translate('richText.openInNewTab', 'yellowpages2') ?? 'Open in a new tab',
            ],
        ];
    }

    private function getLanguageCode(ServerRequestInterface $request): string
    {
        $siteLanguage = $request->getAttribute('language');
        $languageCode = $siteLanguage instanceof SiteLanguage ? $siteLanguage->getLocale()->getLanguageCode() : 'en';

        return preg_match('/^[a-z]{2,3}$/', $languageCode) === 1 ? $languageCode : 'en';
    }

    /**
     * English is the built-in language of CKEditor and has no translation file
     */
    private function getTranslationUrl(string $languageCode, string $translationsPath, ServerRequestInterface $request): string
    {
        if ($languageCode === 'en' || trim($translationsPath) === '') {
            return '';
        }

        $translationFile = rtrim(trim($translationsPath), '/') . '/' . $languageCode . '.js';
        if (!is_file(GeneralUtility::getFileAbsFileName($translationFile))) {
            return '';
        }

        return (string)$this->resourcePublisher->generateUri(
            $this->systemResourceFactory->createPublicResource($translationFile),
            $request,
            new UriGenerationOptions(cacheBusting: false),
        );
    }
}
