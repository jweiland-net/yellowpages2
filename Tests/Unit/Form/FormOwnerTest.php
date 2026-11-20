<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Form;

use JWeiland\Yellowpages2\Form\FormOwner;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
class FormOwnerTest extends UnitTestCase
{
    private FormOwner $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FormOwner();
    }

    #[Test]
    public function markAsOwnFormSetsRenderingOption(): void
    {
        self::assertSame(
            ['renderingOptions' => ['submitButtonLabel' => 'Save', 'formOwner' => 'yellowpages2']],
            $this->subject->markAsOwnForm(['renderingOptions' => ['submitButtonLabel' => 'Save', 'formOwner' => 'other']]),
        );
    }

    #[Test]
    public function isOwnFormWithFormRuntimeOfOwnFormReturnsTrue(): void
    {
        $formRuntime = $this->createMock(FormRuntime::class);
        $formRuntime->method('getFormDefinition')->willReturn($this->buildFormDefinition('yellowpages2'));

        self::assertTrue($this->subject->isOwnForm($formRuntime));
    }

    #[Test]
    public function isOwnFormWithElementOfOwnFormReturnsTrue(): void
    {
        $element = $this->buildFormDefinition('yellowpages2')->getElementByIdentifier('description');

        self::assertTrue($this->subject->isOwnForm($element));
    }

    #[Test]
    public function isOwnFormWithElementOfForeignFormReturnsFalse(): void
    {
        self::assertFalse($this->subject->isOwnForm($this->buildFormDefinition('events2')->getElementByIdentifier('description')));
        self::assertFalse($this->subject->isOwnForm($this->buildFormDefinition('')->getElementByIdentifier('description')));
    }

    #[Test]
    public function isOwnFormWithElementWithoutFormReturnsFalse(): void
    {
        self::assertFalse($this->subject->isOwnForm(new GenericFormElement('description', 'Textarea')));
        self::assertFalse($this->subject->isOwnForm(null));
    }

    private function buildFormDefinition(string $owner): FormDefinition
    {
        $formDefinition = new FormDefinition('form', [
            'formElementsDefinition' => [
                'Form' => [],
                'Page' => ['implementationClassName' => Page::class],
                'Textarea' => ['implementationClassName' => GenericFormElement::class],
            ],
        ]);
        if ($owner !== '') {
            $formDefinition->setRenderingOption('formOwner', $owner);
        }
        $formDefinition->createPage('page')->createElement('description', 'Textarea');

        return $formDefinition;
    }
}
