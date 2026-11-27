<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Hook;

use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Yellowpages2\Form\FormOwner;
use JWeiland\Yellowpages2\Hook\Form\GeocodeAddressHook;
use JWeiland\Yellowpages2\Service\CompanyAccessService;
use JWeiland\Yellowpages2\Service\CompanyPoiCollectionService;
use JWeiland\Yellowpages2\Service\CompanyRecordService;
use JWeiland\Yellowpages2\Tests\Functional\Traits\CompanyFormRequestTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Domain\Runtime\FormState;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
final class GeocodeAddressHookTest extends FunctionalTestCase
{
    use CompanyFormRequestTrait;

    private const ADDRESS = [
        'street' => 'Echterdinger Straße',
        'house-number' => '57',
        'zip' => '70794',
        'city' => 'Filderstadt',
    ];

    protected array $coreExtensionsToLoad = [
        'form',
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/maps2',
        'jweiland/yellowpages2',
    ];

    private GeoCodeService&MockObject $geoCodeService;

    private LoggerInterface&MockObject $logger;

    private FormDefinition $formDefinition;

    private FormState $formState;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/CompanyForm.csv');
        $this->geoCodeService = $this->createMock(GeoCodeService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->formDefinition = $this->buildFormDefinition();
        $this->formState = new FormState();
    }

    #[Test]
    public function afterSubmitOfAddressPageSetsGeocodedPositionForMapStep(): void
    {
        $this->geoCodeService
            ->expects($this->once())
            ->method('getFirstFoundPositionByAddress')
            ->with('Echterdinger Straße 57, 70794 Filderstadt')
            ->willReturn($this->buildPosition(48.1, 9.1));

        $this->submitAddressPage(self::ADDRESS, $this->buildFormRequest());

        self::assertSame(['latitude' => '48.1', 'longitude' => '9.1'], $this->formState->getFormValue('position'));
        self::assertSame(
            'Echterdinger Straße 57, 70794 Filderstadt',
            $this->formState->getFormValue(GeocodeAddressHook::GEOCODED_ADDRESS),
        );
        self::assertFalse($this->formDefinition->getProcessingRule('street')->getProcessingMessages()->hasErrors());
    }

    #[Test]
    public function afterSubmitWithUnknownAddressAddsErrorToFirstAddressElement(): void
    {
        $this->geoCodeService->method('getFirstFoundPositionByAddress')->willReturn(null);
        $this->logger->expects($this->once())->method('warning')->with(
            self::stringContains('could not be geocoded'),
            ['companyUid' => 0, 'feUserUid' => 0],
        );

        $this->submitAddressPage(self::ADDRESS, $this->buildFormRequest());

        $errors = $this->formDefinition->getProcessingRule('street')->getProcessingMessages()->getErrors();
        self::assertCount(1, $errors);
        self::assertSame(1763380803, $errors[0]->getCode());
        self::assertStringContainsString('could not be found', $errors[0]->getMessage());
        self::assertNull($this->formState->getFormValue('position'));
    }

    #[Test]
    public function afterSubmitWithUnchangedAddressKeepsCorrectedPosition(): void
    {
        $this->geoCodeService->expects($this->never())->method('getFirstFoundPositionByAddress');
        $this->formState->setFormValue('position', ['latitude' => '48.2', 'longitude' => '9.2']);
        $this->formState->setFormValue(GeocodeAddressHook::GEOCODED_ADDRESS, 'Echterdinger Straße 57, 70794 Filderstadt');

        $this->submitAddressPage(self::ADDRESS, $this->buildFormRequest());

        self::assertSame(['latitude' => '48.2', 'longitude' => '9.2'], $this->formState->getFormValue('position'));
    }

    #[Test]
    public function afterSubmitWithChangedAddressGeocodesAgain(): void
    {
        $this->geoCodeService->expects($this->once())->method('getFirstFoundPositionByAddress')
            ->willReturn($this->buildPosition(48.3, 9.3));
        $this->formState->setFormValue('position', ['latitude' => '48.2', 'longitude' => '9.2']);
        $this->formState->setFormValue(GeocodeAddressHook::GEOCODED_ADDRESS, 'Hauptstraße 1, 70794 Filderstadt');

        $this->submitAddressPage(self::ADDRESS, $this->buildFormRequest());

        self::assertSame(['latitude' => '48.3', 'longitude' => '9.3'], $this->formState->getFormValue('position'));
    }

    #[Test]
    public function afterSubmitOfEditedCompanyWithUnchangedAddressKeepsStoredPosition(): void
    {
        $this->geoCodeService->expects($this->never())->method('getFirstFoundPositionByAddress');

        $this->submitAddressPage(self::ADDRESS, $this->buildModerationRequest(1));

        self::assertNull($this->formState->getFormValue('position'));
    }

    #[Test]
    public function afterSubmitOfEditedCompanyWithChangedAddressGeocodesAgain(): void
    {
        $this->geoCodeService->expects($this->once())->method('getFirstFoundPositionByAddress')
            ->with('Echterdinger Straße 59, 70794 Filderstadt')
            ->willReturn($this->buildPosition(48.4, 9.4));

        $this->submitAddressPage(['house-number' => '59'] + self::ADDRESS, $this->buildModerationRequest(1));

        self::assertSame(['latitude' => '48.4', 'longitude' => '9.4'], $this->formState->getFormValue('position'));
    }

    #[Test]
    public function afterSubmitOfEditedCompanyWithoutPositionGeocodesAddress(): void
    {
        $this->geoCodeService->expects($this->once())->method('getFirstFoundPositionByAddress')
            ->with('Hauptstraße 1, 70794 Filderstadt')
            ->willReturn($this->buildPosition(48.5, 9.5));

        $address = ['street' => 'Hauptstraße', 'house-number' => '1', 'zip' => '70794', 'city' => 'Filderstadt'];
        $this->submitAddressPage($address, $this->buildModerationRequest(2));

        self::assertSame(['latitude' => '48.5', 'longitude' => '9.5'], $this->formState->getFormValue('position'));
    }

    #[Test]
    public function afterSubmitWithoutCityDoesNotGeocode(): void
    {
        $this->geoCodeService->expects($this->never())->method('getFirstFoundPositionByAddress');

        $this->submitAddressPage(['city' => ''] + self::ADDRESS, $this->buildFormRequest());

        self::assertNull($this->formState->getFormValue('position'));
    }

    #[Test]
    public function afterSubmitOfMapPageDoesNotGeocode(): void
    {
        $this->geoCodeService->expects($this->never())->method('getFirstFoundPositionByAddress');

        $page = $this->formDefinition->getPageByIndex(1);
        $this->buildSubject()->afterSubmit($this->buildFormRuntime($this->buildFormRequest()), $page, null, self::ADDRESS);

        self::assertNull($this->formState->getFormValue('position'));
    }

    #[Test]
    public function afterSubmitOfElementReturnsElementValue(): void
    {
        $element = $this->formDefinition->getElementByIdentifier('street');

        self::assertSame(
            'value',
            $this->buildSubject()->afterSubmit($this->buildFormRuntime($this->buildFormRequest()), $element, 'value', self::ADDRESS),
        );
    }

    /**
     * @param array<string, string> $address
     */
    private function submitAddressPage(array $address, ServerRequestInterface $request): void
    {
        $page = $this->formDefinition->getPageByIndex(0);

        self::assertNull($this->buildSubject()->afterSubmit($this->buildFormRuntime($request), $page, null, $address));
    }

    private function buildSubject(): GeocodeAddressHook
    {
        return new GeocodeAddressHook(
            $this->geoCodeService,
            $this->get(CompanyAccessService::class),
            $this->get(CompanyRecordService::class),
            $this->get(CompanyPoiCollectionService::class),
            $this->get(LanguageServiceFactory::class),
            $this->logger,
        );
    }

    private function buildFormRuntime(ServerRequestInterface $request): FormRuntime
    {
        $formRuntime = $this->createMock(FormRuntime::class);
        $formRuntime->method('getFormDefinition')->willReturn($this->formDefinition);
        $formRuntime->method('getFormState')->willReturn($this->formState);
        $formRuntime->method('getRequest')->willReturn($request);

        return $formRuntime;
    }

    private function buildPosition(float $latitude, float $longitude): Position
    {
        $position = new Position();
        $position->setLatitude($latitude);
        $position->setLongitude($longitude);

        return $position;
    }

    /**
     * Address page and map page with the element "Maps2PoiPosition" of EXT:maps2
     */
    private function buildFormDefinition(): FormDefinition
    {
        $formDefinition = new FormDefinition('yellowpages2Company', [
            'formElementsDefinition' => [
                'Form' => [],
                'Page' => ['implementationClassName' => Page::class],
                'Text' => ['implementationClassName' => GenericFormElement::class],
                'Maps2PoiPosition' => ['implementationClassName' => GenericFormElement::class],
            ],
        ]);

        $formDefinition->setRenderingOption(FormOwner::RENDERING_OPTION, FormOwner::YELLOWPAGES2);

        $addressPage = $formDefinition->createPage('company-data');
        foreach (array_keys(self::ADDRESS) as $identifier) {
            $addressPage->createElement($identifier, 'Text');
        }

        $position = $formDefinition->createPage('map')->createElement('position', 'Maps2PoiPosition');
        $position->setProperty('addressElements', array_keys(self::ADDRESS));

        return $formDefinition;
    }
}
