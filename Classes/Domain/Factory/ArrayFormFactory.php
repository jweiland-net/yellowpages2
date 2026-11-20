<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Factory;

use JWeiland\Yellowpages2\Form\CompanyFormConfigurator;
use JWeiland\Yellowpages2\Form\CompanyFormPrefiller;
use JWeiland\Yellowpages2\Form\FormElementOptionsConfigurator;
use JWeiland\Yellowpages2\Form\FormOwner;
use JWeiland\Yellowpages2\Form\RichTextFormElementConfigurator;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Form\Domain\Factory\ArrayFormFactory as CoreArrayFormFactory;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;

/**
 * Builds the company form based on EXT:form. The form definition is
 * configured for the current request before it is built (form action,
 * finishers, options and rich text elements). Afterward, the elements are
 * prefilled with the values of the company to edit. The rendering option
 * "formOwner" marks the form, so the hooks of yellowpages2 act on forms of
 * this factory only (see FormOwner).
 *
 * Usage in Fluid:
 * <formvh:render persistenceIdentifier="EXT:yellowpages2/Configuration/Form/Company.form.yaml"
 *                factoryClass="JWeiland\Yellowpages2\Domain\Factory\ArrayFormFactory"/>
 */
#[Autoconfigure(public: true, shared: false)]
class ArrayFormFactory extends CoreArrayFormFactory
{
    public function __construct(
        protected readonly CompanyFormConfigurator $companyFormConfigurator,
        protected readonly FormElementOptionsConfigurator $formElementOptionsConfigurator,
        protected readonly RichTextFormElementConfigurator $richTextFormElementConfigurator,
        protected readonly CompanyFormPrefiller $companyFormPrefiller,
        protected readonly FormOwner $formOwner,
    ) {}

    /**
     * @param array<string, mixed> $configuration
     */
    public function build(
        array $configuration,
        ?string $prototypeName = null,
        ?ServerRequestInterface $request = null,
    ): FormDefinition {
        $configuration = $this->formOwner->markAsOwnForm($configuration);
        $configuration = $this->companyFormConfigurator->configure($configuration, $request);
        $configuration = $this->formElementOptionsConfigurator->configure($configuration, $request);
        $configuration = $this->richTextFormElementConfigurator->configure($configuration, $request);

        $formDefinition = parent::build($configuration, $prototypeName, $request);
        if ($request instanceof ServerRequestInterface) {
            $this->companyFormPrefiller->prefill($formDefinition, $request);
        }

        return $formDefinition;
    }
}
