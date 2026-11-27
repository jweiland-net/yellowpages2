<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Form;

use JWeiland\Yellowpages2\Service\CompanyFormOptionsService;
use JWeiland\Yellowpages2\Service\PluginSettingsService;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Adds the options of form elements with "properties.optionsProvider":
 *
 * - "districts": The districts of the storage pages of the plugin. New
 *   companies get the district of TypoScript "settings.uidOfDefaultDistrict"
 *   as default value.
 * - "categories": The child categories of TypoScript
 *   "settings.startingUidForCategories"
 */
final readonly class FormElementOptionsConfigurator
{
    public const PROVIDER_DISTRICTS = 'districts';

    public const PROVIDER_CATEGORIES = 'categories';

    public function __construct(
        private CompanyFormOptionsService $companyFormOptionsService,
        private PluginSettingsService $pluginSettingsService,
    ) {}

    /**
     * @param array<string, mixed> $configuration Form definition
     * @return array<string, mixed>
     */
    public function configure(array $configuration, ?ServerRequestInterface $request): array
    {
        $configuration['renderables'] = $this->configureRenderables(
            is_array($configuration['renderables'] ?? null) ? $configuration['renderables'] : [],
            $request,
        );

        return $configuration;
    }

    /**
     * @param array<int|string, mixed> $renderables
     * @return array<int|string, mixed>
     */
    private function configureRenderables(array $renderables, ?ServerRequestInterface $request): array
    {
        foreach ($renderables as $key => $renderable) {
            if (!is_array($renderable)) {
                continue;
            }

            $provider = $renderable['properties']['optionsProvider'] ?? '';
            if (is_string($provider) && $provider !== '') {
                $renderable = $this->addOptions($renderable, $provider, $request);
            }

            if (is_array($renderable['renderables'] ?? null)) {
                $renderable['renderables'] = $this->configureRenderables($renderable['renderables'], $request);
            }

            $renderables[$key] = $renderable;
        }

        return $renderables;
    }

    /**
     * @param array<string, mixed> $renderable
     * @return array<string, mixed>
     */
    private function addOptions(array $renderable, string $provider, ?ServerRequestInterface $request): array
    {
        $settings = $this->pluginSettingsService->getSettings($request);
        $renderable['properties']['options'] = match ($provider) {
            self::PROVIDER_DISTRICTS => $this->companyFormOptionsService->getDistricts(
                $this->pluginSettingsService->getStoragePids($request),
            ),
            self::PROVIDER_CATEGORIES => $this->companyFormOptionsService->getCategories(
                (int)($settings['startingUidForCategories'] ?? 0),
            ),
            default => $renderable['properties']['options'] ?? [],
        };

        $defaultDistrict = (int)($settings['uidOfDefaultDistrict'] ?? 0);
        if ($provider === self::PROVIDER_DISTRICTS
            && ($renderable['defaultValue'] ?? '') === ''
            && isset($renderable['properties']['options'][$defaultDistrict])
        ) {
            $renderable['defaultValue'] = (string)$defaultDistrict;
        }

        return $renderable;
    }
}
