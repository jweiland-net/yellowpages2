<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Form;

use JWeiland\Yellowpages2\Form\FormElementOptionsConfigurator;
use JWeiland\Yellowpages2\Tests\Functional\Traits\CompanyFormRequestTrait;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class FormElementOptionsConfiguratorTest extends FunctionalTestCase
{
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

    private FormElementOptionsConfigurator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/CompanyForm.csv');
        $this->subject = $this->get(FormElementOptionsConfigurator::class);
    }

    #[Test]
    public function configureAddsDistrictsAndDefaultDistrict(): void
    {
        $element = $this->configureElement('districts');

        self::assertSame([1 => 'Bernhausen', 2 => 'Bonlanden'], $element['properties']['options']);
        self::assertSame('2', $element['defaultValue']);
    }

    #[Test]
    public function configureAddsChildCategoriesOfStartingCategory(): void
    {
        $element = $this->configureElement('categories');

        self::assertSame([2 => 'Bakery', 3 => 'Butcher', 4 => 'Florist'], $element['properties']['options']);
        self::assertArrayNotHasKey('defaultValue', $element);
    }

    #[Test]
    public function configureKeepsElementsWithoutProvider(): void
    {
        $configuration = ['renderables' => [['renderables' => [['identifier' => 'company', 'properties' => ['options' => [1 => 'a']]]]]]];

        self::assertSame($configuration, $this->subject->configure($configuration, $this->buildFormRequest()));
    }

    /**
     * @return array<string, mixed>
     */
    private function configureElement(string $provider): array
    {
        $configuration = $this->subject->configure(
            ['renderables' => [['renderables' => [['identifier' => 'element', 'properties' => ['optionsProvider' => $provider]]]]]],
            $this->buildFormRequest(),
        );

        return $configuration['renderables'][0]['renderables'][0];
    }
}
