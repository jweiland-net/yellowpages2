<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Form;

use JWeiland\Yellowpages2\Form\CompanyFormPrefiller;
use JWeiland\Yellowpages2\Tests\Functional\Traits\CompanyFormDefinitionTrait;
use JWeiland\Yellowpages2\Tests\Functional\Traits\CompanyFormRequestTrait;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
final class CompanyFormPrefillerTest extends FunctionalTestCase
{
    use CompanyFormDefinitionTrait;
    use CompanyFormRequestTrait;

    protected array $coreExtensionsToLoad = [
        'form',
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/maps2',
        'jweiland/yellowpages2',
    ];

    private CompanyFormPrefiller $subject;

    private FormDefinition $formDefinition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/CompanyForm.csv');
        $this->subject = $this->get(CompanyFormPrefiller::class);
        $this->formDefinition = $this->buildCompanyFormDefinition();
    }

    #[Test]
    public function prefillSetsValuesOfEditedCompany(): void
    {
        $this->subject->prefill($this->formDefinition, $this->buildModerationRequest(1));

        self::assertSame('Bakery Müller', $this->getDefaultValue('company'));
        self::assertSame('57', $this->getDefaultValue('house-number'));
        self::assertSame('1', $this->getDefaultValue('district'));
        self::assertSame('Mo-Fr 6-18', $this->getDefaultValue('opening-times'));
    }

    #[Test]
    public function prefillConvertsHtmlOfPlainTextElement(): void
    {
        $this->subject->prefill($this->formDefinition, $this->buildModerationRequest(1));

        self::assertSame("Fresh bread\nevery day", $this->getDefaultValue('description'));
    }

    #[Test]
    public function prefillKeepsHtmlOfRichTextElement(): void
    {
        $this->formDefinition->getElementByIdentifier('description')
            ?->setProperty('dbMapping', ['column' => 'description', 'dataType' => 'richText']);

        $this->subject->prefill($this->formDefinition, $this->buildModerationRequest(1));

        self::assertSame('<p>Fresh <strong>bread</strong><br />every day</p>', $this->getDefaultValue('description'));
    }

    #[Test]
    public function prefillSetsRelatedCategories(): void
    {
        $this->subject->prefill($this->formDefinition, $this->buildModerationRequest(1));

        self::assertSame('2', $this->getDefaultValue('main-trade'));
        self::assertSame(['3', '4'], $this->getDefaultValue('trades'));
    }

    #[Test]
    public function prefillSetsPositionOfPoiCollection(): void
    {
        $this->subject->prefill($this->formDefinition, $this->buildModerationRequest(1));

        self::assertSame(['latitude' => '48.6853', 'longitude' => '9.2148'], $this->getDefaultValue('position'));
    }

    #[Test]
    public function prefillOfCompanyWithoutPoiCollectionKeepsPositionEmpty(): void
    {
        $this->subject->prefill($this->formDefinition, $this->buildModerationRequest(2));

        self::assertSame('Florist without position', $this->getDefaultValue('company'));
        self::assertNull($this->getDefaultValue('position'));
    }

    #[Test]
    public function prefillForOwnerSetsValues(): void
    {
        $this->loginFrontendUser(5);

        $this->subject->prefill($this->formDefinition, $this->buildFormRequest(['company' => '1']));

        self::assertSame('Bakery Müller', $this->getDefaultValue('company'));
    }

    #[Test]
    public function prefillForForeignCompanyKeepsFormEmpty(): void
    {
        $this->loginFrontendUser(6);

        $this->subject->prefill($this->formDefinition, $this->buildFormRequest(['company' => '1']));

        self::assertNull($this->getDefaultValue('company'));
    }

    #[Test]
    public function prefillWithInvalidModerationTokenKeepsFormEmpty(): void
    {
        $this->subject->prefill($this->formDefinition, $this->buildFormRequest(['company' => '1', 'moderationToken' => 'invalid']));

        self::assertNull($this->getDefaultValue('company'));
    }

    #[Test]
    public function prefillOfNewCompanyKeepsFormEmpty(): void
    {
        $this->loginFrontendUser(5);

        $this->subject->prefill($this->formDefinition, $this->buildFormRequest());

        self::assertNull($this->getDefaultValue('company'));
    }

    private function getDefaultValue(string $identifier): mixed
    {
        return $this->formDefinition->getElementDefaultValueByIdentifier($identifier);
    }
}
