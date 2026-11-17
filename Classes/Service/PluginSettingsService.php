<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Reads the TypoScript settings of the Management plugin outside of Extbase,
 * e.g. in the finishers and the factory of the company form based on
 * EXT:form. Like Extbase, plugin.tx_yellowpages2_management overrides
 * plugin.tx_yellowpages2.
 */
readonly class PluginSettingsService
{
    private const PLUGIN_KEYS = ['tx_yellowpages2.', 'tx_yellowpages2_management.'];

    public function __construct(
        private TypoScriptService $typoScriptService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getSettings(?ServerRequestInterface $request): array
    {
        $pluginSetup = $this->getPluginSetup($request);

        $settings = [];
        foreach (self::PLUGIN_KEYS as $pluginKey) {
            $pluginSettings = $pluginSetup[$pluginKey]['settings.'] ?? null;
            if (is_array($pluginSettings)) {
                ArrayUtility::mergeRecursiveWithOverrule(
                    $settings,
                    $this->typoScriptService->convertTypoScriptArrayToPlainArray($pluginSettings),
                );
            }
        }

        return $settings;
    }

    /**
     * The storage pages of "persistence.storagePid" (not recursive)
     *
     * @return int[]
     */
    public function getStoragePids(?ServerRequestInterface $request): array
    {
        $pluginSetup = $this->getPluginSetup($request);
        $storagePid = '';
        foreach (self::PLUGIN_KEYS as $pluginKey) {
            $storagePid = (string)($pluginSetup[$pluginKey]['persistence.']['storagePid'] ?? $storagePid);
        }

        return GeneralUtility::intExplode(',', $storagePid, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function getPluginSetup(?ServerRequestInterface $request): array
    {
        $frontendTypoScript = $request?->getAttribute('frontend.typoscript');
        if (!$frontendTypoScript instanceof FrontendTypoScript) {
            return [];
        }

        try {
            $pluginSetup = $frontendTypoScript->getSetupArray()['plugin.'] ?? [];
        } catch (\RuntimeException) {
            // The setup is not available in cached frontend scope
            return [];
        }

        return is_array($pluginSetup) ? $pluginSetup : [];
    }
}
