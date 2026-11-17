<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Traits;

use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;

/**
 * Builds a reduced company form with an address page and a map page. The
 * element "Maps2PoiPosition" of EXT:maps2 is simulated by a generic element.
 */
trait CompanyFormDefinitionTrait
{
    private function buildCompanyFormDefinition(): FormDefinition
    {
        $formDefinition = new FormDefinition('yellowpages2Company', [
            'formElementsDefinition' => [
                'Form' => [],
                'Page' => ['implementationClassName' => Page::class],
                'Text' => ['implementationClassName' => GenericFormElement::class],
                'Textarea' => ['implementationClassName' => GenericFormElement::class],
                'Checkbox' => ['implementationClassName' => GenericFormElement::class],
                'SingleSelect' => ['implementationClassName' => GenericFormElement::class],
                'MultiCheckbox' => ['implementationClassName' => GenericFormElement::class],
                'Maps2PoiPosition' => ['implementationClassName' => GenericFormElement::class],
            ],
        ]);

        $page = $formDefinition->createPage('company-data');
        foreach (['company' => 'company', 'street' => 'street', 'house-number' => 'house_number', 'zip' => 'zip', 'city' => 'city'] as $identifier => $column) {
            $page->createElement($identifier, 'Text')->setProperty('dbMapping', ['column' => $column]);
        }

        $this->addElement($page, 'district', 'SingleSelect', ['column' => 'district'], [1 => 'Bernhausen', 2 => 'Bonlanden']);
        $this->addElement($page, 'description', 'Textarea', ['column' => 'description', 'dataType' => 'plainText']);
        $this->addElement($page, 'opening-times', 'Textarea', ['column' => 'opening_times', 'dataType' => 'plainText']);
        $this->addElement($page, 'barrier-free', 'Checkbox', ['column' => 'barrier_free']);
        $this->addElement($page, 'main-trade', 'SingleSelect', ['column' => 'main_trade'], [2 => 'Bakery', 3 => 'Butcher', 4 => 'Florist']);
        $this->addElement($page, 'trades', 'MultiCheckbox', ['column' => 'trades'], [2 => 'Bakery', 3 => 'Butcher', 4 => 'Florist']);

        $this->addElement($formDefinition->createPage('map'), 'position', 'Maps2PoiPosition', ['column' => 'tx_maps2_uid', 'dataType' => 'poiPosition']);

        return $formDefinition;
    }

    /**
     * @param array<string, string> $dbMapping
     * @param array<int, string> $options
     */
    private function addElement(Page $page, string $identifier, string $type, array $dbMapping, array $options = []): void
    {
        $element = $page->createElement($identifier, $type);
        $element->setProperty('dbMapping', $dbMapping);
        if ($options !== []) {
            $element->setProperty('options', $options);
        }
    }

    /**
     * The "elements" option of the SaveCompanyFinisher of Company.form.yaml
     *
     * @return array<string, array<string, mixed>>
     */
    private function getElementsConfiguration(): array
    {
        return [
            'company' => ['mapOnDatabaseColumn' => 'company'],
            'street' => ['mapOnDatabaseColumn' => 'street'],
            'house-number' => ['mapOnDatabaseColumn' => 'house_number'],
            'zip' => ['mapOnDatabaseColumn' => 'zip'],
            'city' => ['mapOnDatabaseColumn' => 'city'],
            'district' => ['mapOnDatabaseColumn' => 'district'],
            'description' => ['mapOnDatabaseColumn' => 'description', 'convertPlainTextToHtml' => true],
            'opening-times' => ['mapOnDatabaseColumn' => 'opening_times', 'normalizePlainText' => true],
            'barrier-free' => ['mapOnDatabaseColumn' => 'barrier_free'],
            'main-trade' => ['mapOnDatabaseColumn' => 'main_trade'],
            'trades' => ['mapOnDatabaseColumn' => 'trades'],
            'position' => ['poiPosition' => true],
        ];
    }
}
