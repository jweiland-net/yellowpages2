<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Hook\Form;

use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Yellowpages2\Form\FormOwner;
use JWeiland\Yellowpages2\Form\PoiPosition;
use JWeiland\Yellowpages2\Service\CompanyAccessService;
use JWeiland\Yellowpages2\Service\CompanyPoiCollectionService;
use JWeiland\Yellowpages2\Service\CompanyRecordService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Geocodes the address of the company, when the page with the address of
 * a form of yellowpages2 (see FormOwner) is submitted. The element "Maps2PoiPosition" of EXT:maps2 on a following
 * page (the map step) gets the found position, so the user can correct it.
 * The address is built of the submitted values of the elements in
 * "properties.addressElements" of the position element.
 *
 * The address is geocoded again only, if it was changed. So a corrected
 * position is kept, if the user goes back to the address. The stored
 * position of an edited company is kept, as long as its address is not
 * changed. If the address can not be geocoded, the user stays on the page
 * with the address and gets an error at its first element.
 */
final readonly class GeocodeAddressHook
{
    public const ELEMENT_TYPE = 'Maps2PoiPosition';

    /**
     * Key in the form state with the last geocoded address
     */
    public const GEOCODED_ADDRESS = '__yellowpages2GeocodedAddress';

    private const ERROR_LABEL = 'LLL:EXT:yellowpages2/Resources/Private/Language/locallang.xlf:errorAddressInvalid.body';

    public function __construct(
        private GeoCodeService $geoCodeService,
        private CompanyAccessService $companyAccessService,
        private CompanyRecordService $companyRecordService,
        private CompanyPoiCollectionService $companyPoiCollectionService,
        private LanguageServiceFactory $languageServiceFactory,
        private FormOwner $formOwner,
    ) {}

    /**
     * Called by EXT:form for the submitted page first, then for each element of this page
     *
     * @param array<string, mixed> $requestArguments
     */
    public function afterSubmit(FormRuntime $formRuntime, RenderableInterface $renderable, mixed $elementValue, array $requestArguments = []): mixed
    {
        $positionElement = $renderable instanceof Page && $this->formOwner->isOwnForm($formRuntime)
            ? $this->getPositionElement($formRuntime->getFormDefinition(), $renderable)
            : null;
        if (!$positionElement instanceof FormElementInterface) {
            return $elementValue;
        }

        $addressElements = $this->getAddressElements($positionElement);
        $address = $this->buildAddress($addressElements, $requestArguments);
        if ($address !== '' && $address !== $this->getGeocodedAddress($formRuntime)) {
            $this->geocode($formRuntime, $positionElement, $address, (string)($addressElements[0] ?? ''));
        }

        return $elementValue;
    }

    private function geocode(FormRuntime $formRuntime, FormElementInterface $positionElement, string $address, string $errorElement): void
    {
        $position = $this->geoCodeService->getFirstFoundPositionByAddress($address);
        if (!$position instanceof Position) {
            $formRuntime->getFormDefinition()->getProcessingRule($errorElement)->getProcessingMessages()->addError(
                new Error($this->translateError($formRuntime), 1763380803),
            );
            return;
        }

        $formRuntime->getFormState()?->setFormValue(
            $positionElement->getIdentifier(),
            (new PoiPosition($position->getLatitude(), $position->getLongitude()))->toFormValue(),
        );
        $formRuntime->getFormState()?->setFormValue(self::GEOCODED_ADDRESS, $address);
    }

    /**
     * Without a geocoded address in the form state, the address of the
     * edited company counts as geocoded, if it has a position already.
     */
    private function getGeocodedAddress(FormRuntime $formRuntime): string
    {
        $geocodedAddress = $formRuntime->getFormState()?->getFormValue(self::GEOCODED_ADDRESS);
        if (is_string($geocodedAddress)) {
            return $geocodedAddress;
        }

        $company = $this->companyRecordService->findCompany($this->companyAccessService->getCompanyUid($formRuntime->getRequest()));
        $hasPosition = $this->companyPoiCollectionService->getPosition((int)($company['tx_maps2_uid'] ?? 0)) instanceof PoiPosition;

        return $hasPosition ? $this->companyPoiCollectionService->getAddress($company) : '';
    }

    /**
     * The first position element on a page after the submitted page
     */
    private function getPositionElement(FormDefinition $formDefinition, Page $page): ?FormElementInterface
    {
        foreach ($formDefinition->getPages() as $followingPage) {
            if ($followingPage->getIndex() <= $page->getIndex()) {
                continue;
            }
            foreach ($followingPage->getElementsRecursively() as $element) {
                if ($element->getType() === self::ELEMENT_TYPE) {
                    return $element;
                }
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getAddressElements(FormElementInterface $positionElement): array
    {
        $addressElements = $positionElement->getProperties()['addressElements'] ?? [];

        return is_array($addressElements) ? array_values(array_filter($addressElements, is_string(...))) : [];
    }

    /**
     * Same format as the address of the stored company, e.g.
     * "Echterdinger Straße 57, 70794 Filderstadt"
     *
     * @param string[] $addressElements Identifiers of street, house number, zip and city
     * @param array<string, mixed> $requestArguments
     */
    private function buildAddress(array $addressElements, array $requestArguments): string
    {
        $values = array_map(
            static fn(string $identifier): string => is_scalar($requestArguments[$identifier] ?? null) ? trim((string)$requestArguments[$identifier]) : '',
            array_pad(array_slice($addressElements, 0, 4), 4, ''),
        );

        return in_array('', [$values[0], $values[3]], true) ? '' : trim(sprintf('%s %s, %s %s', ...$values), ' ,');
    }

    private function translateError(FormRuntime $formRuntime): string
    {
        $language = $formRuntime->getRequest()->getAttribute('language');
        $languageService = $language instanceof SiteLanguage
            ? $this->languageServiceFactory->createFromSiteLanguage($language)
            : $this->languageServiceFactory->create('default');

        return $languageService->sL(self::ERROR_LABEL);
    }
}
