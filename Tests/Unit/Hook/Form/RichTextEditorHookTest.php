<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Hook\Form;

use JWeiland\Yellowpages2\Event\ModifyRichTextSanitizerConfigurationEvent;
use JWeiland\Yellowpages2\Form\FormOwner;
use JWeiland\Yellowpages2\Form\RichTextEditorOptions;
use JWeiland\Yellowpages2\Hook\Form\RichTextEditorHook;
use JWeiland\Yellowpages2\Sanitizer\RichTextSanitizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
final class RichTextEditorHookTest extends UnitTestCase
{
    private RichTextEditorOptions&MockObject $richTextEditorOptions;

    private FormRuntime&MockObject $formRuntime;

    private RichTextEditorHook $subject;

    /**
     * @var ModifyRichTextSanitizerConfigurationEvent[]
     */
    private array $dispatchedEvents = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->richTextEditorOptions = $this->createMock(RichTextEditorOptions::class);
        $this->richTextEditorOptions
            ->method('getEditorOptions')
            ->willReturnOnConsecutiveCalls(['language' => 'de'], ['language' => 'en']);

        $this->formRuntime = $this->createMock(FormRuntime::class);
        $this->formRuntime->method('getRequest')->willReturn($this->createMock(Request::class));
        $ownFormDefinition = new FormDefinition('yellowpages2Company');
        $ownFormDefinition->setRenderingOption(FormOwner::RENDERING_OPTION, FormOwner::YELLOWPAGES2);
        $this->formRuntime->method('getFormDefinition')->willReturn($ownFormDefinition);

        $this->subject = new RichTextEditorHook($this->richTextEditorOptions, new RichTextSanitizer($this->createEventDispatcher()));
    }

    private function createEventDispatcher(): EventDispatcherInterface
    {
        $dispatchedEvents = &$this->dispatchedEvents;

        return new class ($dispatchedEvents) implements EventDispatcherInterface {
            /**
             * @param ModifyRichTextSanitizerConfigurationEvent[] $dispatchedEvents
             */
            public function __construct(private array &$dispatchedEvents) {}

            public function dispatch(object $event): object
            {
                if ($event instanceof ModifyRichTextSanitizerConfigurationEvent) {
                    $event->setAllowedTags([...$event->getAllowedTags(), 'h4']);
                    $this->dispatchedEvents[] = $event;
                }

                return $event;
            }
        };
    }

    #[Test]
    public function beforeRenderingPassesConfigurationOfColumnToEditorOptions(): void
    {
        $element = $this->buildElement('description', 'richText');
        $this->richTextEditorOptions
            ->expects($this->once())
            ->method('getEditorOptions')
            ->with(
                self::anything(),
                self::callback(static fn($configuration): bool => in_array('h4', $configuration->allowedTags, true)),
            );

        $this->subject->beforeRendering($this->formRuntime, $element);

        self::assertCount(1, $this->dispatchedEvents);
        self::assertSame('tx_yellowpages2_domain_model_company', $this->dispatchedEvents[0]->getTable());
        self::assertSame('description', $this->dispatchedEvents[0]->getColumn());
    }

    #[Test]
    public function beforeRenderingAddsEditorToRichTextElement(): void
    {
        $element = $this->buildElement('description', 'richText');

        $this->subject->beforeRendering($this->formRuntime, $element);

        self::assertSame(
            ['class' => 'x', 'data-yellowpages2-rich-text' => '{"language":"de"}'],
            $element->getProperties()['fluidAdditionalAttributes'],
        );
    }

    #[Test]
    public function beforeRenderingAddsEditorToEachRichTextElement(): void
    {
        $firstElement = $this->buildElement('description', 'richText');
        $secondElement = $this->buildElement('program', 'richText');

        $this->subject->beforeRendering($this->formRuntime, $firstElement);
        $this->subject->beforeRendering($this->formRuntime, $secondElement);

        self::assertSame('{"language":"de"}', $firstElement->getProperties()['fluidAdditionalAttributes']['data-yellowpages2-rich-text']);
        self::assertSame('{"language":"en"}', $secondElement->getProperties()['fluidAdditionalAttributes']['data-yellowpages2-rich-text']);
    }

    #[Test]
    public function beforeRenderingIgnoresPlainTextElement(): void
    {
        $element = $this->buildElement('opening-times', 'plainText');
        $this->richTextEditorOptions->expects($this->never())->method('getEditorOptions');

        $this->subject->beforeRendering($this->formRuntime, $element);

        self::assertSame(['class' => 'x'], $element->getProperties()['fluidAdditionalAttributes']);
    }

    #[Test]
    public function beforeRenderingIgnoresFormDefinition(): void
    {
        $this->richTextEditorOptions->expects($this->never())->method('getEditorOptions');

        $this->subject->beforeRendering($this->formRuntime, new FormDefinition('yellowpages2Company'));
    }

    private function buildElement(string $identifier, string $dataType): GenericFormElement
    {
        $element = new GenericFormElement($identifier, 'Textarea');
        $element->setProperty('dbMapping', ['column' => 'description', 'dataType' => $dataType]);
        $element->setProperty('fluidAdditionalAttributes', ['class' => 'x']);

        return $element;
    }
}
