<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Event;

use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

final class PreProcessControllerActionEvent implements ControllerActionEventInterface
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private RequestInterface $request,
        private Arguments $arguments,
        private readonly array $settings,
    ) {}

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getControllerName(): string
    {
        return $this->request->getControllerName();
    }

    public function getActionName(): string
    {
        return $this->request->getControllerActionName();
    }

    public function getArguments(): Arguments
    {
        return $this->arguments;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

    public function setArguments(Arguments $arguments): void
    {
        $this->arguments = $arguments;
    }
}
