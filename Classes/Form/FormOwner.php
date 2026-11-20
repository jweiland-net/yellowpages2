<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Form;

use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * The hooks of EXT:form are called for all forms of the installation. They
 * must only act on the forms of yellowpages2, which are built by the
 * ArrayFormFactory of yellowpages2. The factory sets the rendering option
 * "formOwner" before the form is built, so it is available in all hooks,
 * incl. "initializeFormElement".
 */
readonly class FormOwner
{
    public const RENDERING_OPTION = 'formOwner';

    public const OWNER = 'yellowpages2';

    /**
     * @param array<string, mixed> $configuration Form definition
     * @return array<string, mixed>
     */
    public function markAsOwnForm(array $configuration): array
    {
        $configuration['renderingOptions'][self::RENDERING_OPTION] = self::OWNER;

        return $configuration;
    }

    /**
     * Accepts the form runtime of the hooks "beforeRendering" and
     * "afterSubmit" or a renderable of any other hook
     */
    public function isOwnForm(FormRuntime|RenderableInterface|null $subject): bool
    {
        $formDefinition = $subject instanceof FormRuntime
            ? $subject->getFormDefinition()
            : $this->findFormDefinition($subject);

        return ($formDefinition?->getRenderingOptions()[self::RENDERING_OPTION] ?? '') === self::OWNER;
    }

    /**
     * The renderable may not be attached to a form yet
     */
    private function findFormDefinition(?RenderableInterface $renderable): ?FormDefinition
    {
        while ($renderable !== null && !$renderable instanceof FormDefinition) {
            $renderable = $renderable->getParentRenderable();
        }

        return $renderable;
    }
}
