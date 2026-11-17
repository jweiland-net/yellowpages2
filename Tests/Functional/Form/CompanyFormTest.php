<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Form;

use JWeiland\Yellowpages2\Domain\Factory\ArrayFormFactory;
use JWeiland\Yellowpages2\Tests\Functional\Traits\CompanyFormRequestTrait;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Builds the complete company form of Company.form.yaml with the
 * prototype of EXT:form incl. the form setup of EXT:maps2.
 *
 * The map step needs the form element "Maps2PoiPosition" of EXT:maps2
 * 12.3.0. The tests are skipped, as long as an older version of EXT:maps2
 * is installed. MAPS2_FORM_SETUP has to match the form setup of EXT:maps2.
 */
class CompanyFormTest extends FunctionalTestCase
{
    use CompanyFormRequestTrait;

    private const MAPS2_FORM_SETUP = 'EXT:maps2/Configuration/Form/FormSetup.yaml';

    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/maps2',
        'jweiland/yellowpages2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (version_compare(ExtensionManagementUtility::getExtensionVersion('maps2'), '12.3.0', '<')) {
            self::markTestSkipped('The form element "Maps2PoiPosition" of EXT:maps2 >= 12.3.0 is not available.');
        }

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/CompanyForm.csv');
    }

    #[Test]
    public function buildCreatesAddressPageAndMapStep(): void
    {
        $this->loginFrontendUser(6);

        $formDefinition = $this->buildForm($this->buildFormRequest());

        self::assertSame('company-data', $formDefinition->getPageByIndex(0)->getIdentifier());
        self::assertSame('map', $formDefinition->getPageByIndex(1)->getIdentifier());
        self::assertSame('Maps2PoiPosition', $formDefinition->getElementByIdentifier('position')?->getType());
    }

    #[Test]
    public function buildPrefillsMapStepOfEditedCompany(): void
    {
        $formDefinition = $this->buildForm($this->buildModerationRequest(1));

        self::assertSame(
            ['latitude' => '48.6853', 'longitude' => '9.2148'],
            $formDefinition->getElementDefaultValueByIdentifier('position'),
        );
        self::assertSame('Bakery Müller', $formDefinition->getElementDefaultValueByIdentifier('company'));
    }

    #[Test]
    public function buildAddsOptionsOfDistrictsAndTrades(): void
    {
        $this->loginFrontendUser(6);

        $formDefinition = $this->buildForm($this->buildFormRequest());

        self::assertSame([1 => 'Bernhausen', 2 => 'Bonlanden'], $formDefinition->getElementByIdentifier('district')?->getProperties()['options']);
        self::assertSame([2 => 'Bakery', 3 => 'Butcher', 4 => 'Florist'], $formDefinition->getElementByIdentifier('trades')?->getProperties()['options']);
    }

    private function buildForm(ServerRequestInterface $request): FormDefinition
    {
        $frontendTypoScript = $request->getAttribute('frontend.typoscript');
        $setup = $frontendTypoScript->getSetupArray();
        $setup['plugin.']['tx_form.']['settings.']['yamlConfigurations.'] = [
            '10' => 'EXT:form/Configuration/Yaml/FormSetup.yaml',
            '20' => self::MAPS2_FORM_SETUP,
            '30' => 'EXT:yellowpages2/Configuration/Form/OverrideFormSettings.yaml',
        ];
        $frontendTypoScript->setSetupArray($setup);
        $this->get(ConfigurationManagerInterface::class)->setRequest($request);

        $configuration = Yaml::parseFile(__DIR__ . '/../../../Configuration/Form/Company.form.yaml');

        return $this->get(ArrayFormFactory::class)->build($configuration, null, $request);
    }
}
