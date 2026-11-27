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
 * prototype of EXT:form incl. the auto-discovered form sets of EXT:maps2 and EXT:yellowpages2.
 *
 * The map step needs the form element "Maps2PoiPosition" of EXT:maps2
 * 12.3.0. The tests are skipped, as long as an older version of EXT:maps2
 * is installed.
 */
class CompanyFormTest extends FunctionalTestCase
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

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'yellowpages2' => [
                'emailFromAddress' => 'sender@example.com',
                'emailFromName' => 'Sender',
                'emailToAddress' => 'moderator@example.com',
                'emailToName' => 'Moderator',
            ],
        ],
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
        self::assertSame('yellowpages2', $formDefinition->getRenderingOptions()['formOwner']);
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
        $this->get(ConfigurationManagerInterface::class)->setRequest($request);

        $configuration = Yaml::parseFile(__DIR__ . '/../../../Configuration/Form/Company.form.yaml');

        return $this->get(ArrayFormFactory::class)->build($configuration, null, $request);
    }
}
