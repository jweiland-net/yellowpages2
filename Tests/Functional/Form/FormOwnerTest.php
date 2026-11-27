<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Form;

use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Yellowpages2\Form\FormOwner;
use JWeiland\Yellowpages2\Hook\Form\GeocodeAddressHook;
use JWeiland\Yellowpages2\Service\CompanyAccessService;
use JWeiland\Yellowpages2\Service\CompanyPoiCollectionService;
use JWeiland\Yellowpages2\Service\CompanyRecordService;
use JWeiland\Yellowpages2\Tests\Functional\Fixtures\Classes\ForeignRichTextEditorHook;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Domain\Runtime\FormState;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The hooks of EXT:form are called for all forms of the installation. The
 * hooks of yellowpages2 must act on forms of yellowpages2 only.
 */
final class FormOwnerTest extends FunctionalTestCase
{
    private const ADDRESS = [
        'street' => 'Echterdinger Straße',
        'house-number' => '57',
        'zip' => '70794',
        'city' => 'Filderstadt',
    ];
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

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeRendering'][1763600000]
            = ForeignRichTextEditorHook::class;
    }

    #[Test]
    public function ownFormGetsExactlyOneRichTextEditor(): void
    {
        $formDefinition = $this->buildFormDefinition(FormOwner::YELLOWPAGES2);

        $this->renderForm($formDefinition);

        self::assertSame(['data-yellowpages2-rich-text'], $this->getRichTextAttributes($formDefinition));
    }

    #[Test]
    public function formOfOtherExtensionGetsItsOwnRichTextEditorOnly(): void
    {
        $formDefinition = $this->buildFormDefinition(ForeignRichTextEditorHook::OWNER);

        $this->renderForm($formDefinition);

        self::assertSame([ForeignRichTextEditorHook::DATA_ATTRIBUTE], $this->getRichTextAttributes($formDefinition));
    }

    #[Test]
    public function formOfCoreIsNotModified(): void
    {
        $formDefinition = $this->buildFormDefinition('');

        $this->renderForm($formDefinition);

        self::assertSame([], $this->getRichTextAttributes($formDefinition));
        self::assertSame(['class' => 'form-control'], $this->getDescription($formDefinition)->getProperties()['fluidAdditionalAttributes']);
    }

    #[Test]
    public function addressOfFormOfCoreIsNotGeocoded(): void
    {
        $geoCodeService = $this->createMock(GeoCodeService::class);
        $geoCodeService->expects($this->never())->method('getFirstFoundPositionByAddress');
        $formDefinition = $this->buildFormDefinition('');
        $formState = new FormState();

        $this->buildGeocodeAddressHook($geoCodeService)->afterSubmit(
            $this->buildFormRuntime($formDefinition, $formState),
            $formDefinition->getPageByIndex(0),
            null,
            self::ADDRESS,
        );

        self::assertNull($formState->getFormValue('position'));
        self::assertFalse($formDefinition->getProcessingRule('street')->getProcessingMessages()->hasErrors());
    }

    #[Test]
    public function addressOfOwnFormIsGeocoded(): void
    {
        $geoCodeService = $this->createMock(GeoCodeService::class);
        $geoCodeService->expects($this->once())->method('getFirstFoundPositionByAddress')->willReturn(null);
        $formDefinition = $this->buildFormDefinition(FormOwner::YELLOWPAGES2);

        $this->buildGeocodeAddressHook($geoCodeService)->afterSubmit(
            $this->buildFormRuntime($formDefinition, new FormState()),
            $formDefinition->getPageByIndex(0),
            null,
            self::ADDRESS,
        );
    }

    /**
     * Calls all registered "beforeRendering" hooks for each element like FormRuntime does
     */
    private function renderForm(FormDefinition $formDefinition): void
    {
        $formRuntime = $this->buildFormRuntime($formDefinition, new FormState());
        foreach ($formDefinition->getRenderablesRecursively() as $renderable) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeRendering'] ?? [] as $className) {
                $hook = GeneralUtility::makeInstance($className);
                if (method_exists($hook, 'beforeRendering')) {
                    $hook->beforeRendering($formRuntime, $renderable);
                }
            }
        }
    }

    /**
     * @return string[]
     */
    private function getRichTextAttributes(FormDefinition $formDefinition): array
    {
        $attributes = $this->getDescription($formDefinition)->getProperties()['fluidAdditionalAttributes'] ?? [];

        return array_values(array_filter(
            array_keys($attributes),
            static fn(string $attribute): bool => str_ends_with($attribute, '-rich-text'),
        ));
    }

    private function getDescription(FormDefinition $formDefinition): FormElementInterface
    {
        $description = $formDefinition->getElementByIdentifier('description');
        self::assertInstanceOf(FormElementInterface::class, $description);

        return $description;
    }

    private function buildGeocodeAddressHook(GeoCodeService $geoCodeService): GeocodeAddressHook
    {
        return new GeocodeAddressHook(
            $geoCodeService,
            $this->get(CompanyAccessService::class),
            $this->get(CompanyRecordService::class),
            $this->get(CompanyPoiCollectionService::class),
            $this->get(LanguageServiceFactory::class),
            new NullLogger(),
        );
    }

    private function buildFormRuntime(FormDefinition $formDefinition, FormState $formState): FormRuntime
    {
        $formRuntime = $this->createMock(FormRuntime::class);
        $formRuntime->method('getFormDefinition')->willReturn($formDefinition);
        $formRuntime->method('getFormState')->willReturn($formState);
        $formRuntime->method('getRequest')->willReturn($this->createMock(Request::class));

        return $formRuntime;
    }

    /**
     * A form with a rich text element, an address and a map step like the
     * company form. $owner is the rendering option "formOwner".
     */
    private function buildFormDefinition(string $owner): FormDefinition
    {
        $formDefinition = new FormDefinition('contact', [
            'formElementsDefinition' => [
                'Form' => [],
                'Page' => ['implementationClassName' => Page::class],
                'Text' => ['implementationClassName' => GenericFormElement::class],
                'Textarea' => ['implementationClassName' => GenericFormElement::class],
                'Maps2PoiPosition' => ['implementationClassName' => GenericFormElement::class],
            ],
        ]);
        if ($owner !== '') {
            $formDefinition->setRenderingOption(FormOwner::RENDERING_OPTION, $owner);
        }

        $page = $formDefinition->createPage('address');
        foreach (array_keys(self::ADDRESS) as $identifier) {
            $page->createElement($identifier, 'Text');
        }
        $description = $page->createElement('description', 'Textarea');
        $description->setProperty('dbMapping', ['column' => 'description', 'dataType' => 'richText']);
        $description->setProperty('fluidAdditionalAttributes', ['class' => 'form-control']);

        $position = $formDefinition->createPage('map')->createElement('position', 'Maps2PoiPosition');
        $position->setProperty('addressElements', array_keys(self::ADDRESS));

        return $formDefinition;
    }
}
