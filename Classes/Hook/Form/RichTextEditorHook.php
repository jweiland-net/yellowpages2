<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Hook\Form;

use JWeiland\Yellowpages2\Form\FormOwner;
use JWeiland\Yellowpages2\Form\RichTextEditorAssets;
use JWeiland\Yellowpages2\Form\RichTextFormElementConfigurator;
use JWeiland\Yellowpages2\Sanitizer\RichTextSanitizer;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Adds the CKEditor to the rich text elements (dataType "richText"), which
 * are rendered on the current page of a form of yellowpages2 (see
 * FormOwner). So the assets are loaded on
 * these pages only. Each textarea gets its own options as JSON.
 *
 * The toolbar of the editor is based on the tags, which the sanitizer allows
 * for the column of the element (see ModifyRichTextSanitizerConfigurationEvent).
 * The column is "properties.dbMapping.column", the table is
 * "properties.dbMapping.table" (default: tx_yellowpages2_domain_model_company).
 */
final readonly class RichTextEditorHook
{
    public const DATA_ATTRIBUTE = 'data-yellowpages2-rich-text';

    private const DEFAULT_TABLE = 'tx_yellowpages2_domain_model_company';

    public function __construct(
        private RichTextEditorAssets $richTextEditorAssets,
        private RichTextSanitizer $richTextSanitizer,
    ) {}

    public function beforeRendering(FormRuntime $formRuntime, RootRenderableInterface $renderable): void
    {
        if (
            !FormOwner::isYellowpages2Form($formRuntime->getFormDefinition())
            || !$renderable instanceof FormElementInterface
            || ($renderable->getProperties()['dbMapping']['dataType'] ?? '') !== RichTextFormElementConfigurator::DATA_TYPE
        ) {
            return;
        }

        $dbMapping = $renderable->getProperties()['dbMapping'];
        $configuration = $this->richTextSanitizer->getConfiguration(
            (string)($dbMapping['table'] ?? self::DEFAULT_TABLE),
            (string)($dbMapping['column'] ?? ''),
        );

        $renderable->setProperty('fluidAdditionalAttributes', [
            self::DATA_ATTRIBUTE => json_encode(
                $this->richTextEditorAssets->getEditorOptions($formRuntime->getRequest(), $configuration),
                JSON_THROW_ON_ERROR,
            ),
        ]);
        $this->richTextEditorAssets->load();
    }
}
