<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Backend\Preview;

use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;

/**
 * Add plugin preview for EXT:yellowpages2
 */
class Yellowpages2PluginPreview extends StandardContentPreviewRenderer
{
    private const PREVIEW_TEMPLATE = 'EXT:yellowpages2/Resources/Private/Templates/PluginPreview/YellowPages2.html';

    private const ALLOWED_PLUGINS = [
        'yellowpages2_directory',
    ];

    public function __construct(
        protected FlexFormService $flexFormService,
        protected ViewFactoryInterface $viewFactory,
    ) {}

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $ttContentRecord = $item->getRecord();
        if (!$this->isValidPlugin($ttContentRecord)) {
            return '';
        }

        $view = $this->viewFactory->create(
            new ViewFactoryData(
                templatePathAndFilename: self::PREVIEW_TEMPLATE,
            ),
        );
        $view->assignMultiple($ttContentRecord);

        $this->addPluginName($view, $ttContentRecord);

        // Add data from column pi_flexform
        $piFlexformData = $this->getPiFlexformData($ttContentRecord);
        if ($piFlexformData !== []) {
            $view->assign('pi_flexform_transformed', $piFlexformData);
        }

        return $view->render();
    }

    /**
     * @param array<string, mixed> $ttContentRecord
     */
    protected function isValidPlugin(array $ttContentRecord): bool
    {
        if (!isset($ttContentRecord['CType'])) {
            return false;
        }

        if (!in_array($ttContentRecord['CType'], self::ALLOWED_PLUGINS, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $ttContentRecord
     */
    protected function addPluginName(ViewInterface $view, array $ttContentRecord): void
    {
        $langKey = sprintf(
            'plugin.%s.title',
            str_replace('yellowpages2_', '', $ttContentRecord['CType']),
        );

        $view->assign(
            'pluginName',
            $this->getLanguageService()->sL('LLL:EXT:yellowpages2/Resources/Private/Language/locallang_db.xlf:' . $langKey),
        );
    }

    /**
     * @param array<string, mixed> $ttContentRecord
     * @return array<string, mixed>
     */
    protected function getPiFlexformData(array $ttContentRecord): array
    {
        $data = [];
        if (!empty($ttContentRecord['pi_flexform'] ?? '')) {
            $data = $this->flexFormService->convertFlexFormContentToArray($ttContentRecord['pi_flexform']);
        }

        return $data;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
