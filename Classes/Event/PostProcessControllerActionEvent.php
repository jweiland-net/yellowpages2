<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Event;

use JWeiland\Yellowpages2\Controller\CompanyController;
use JWeiland\Yellowpages2\Controller\ManagementController;
use JWeiland\Yellowpages2\Controller\MapController;
use JWeiland\Yellowpages2\Domain\Model\Company;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Post-process controller actions that do not assign any variables to view.
 * Often used by controller actions like "update" or "create", which redirects after success.
 */
final readonly class PostProcessControllerActionEvent implements ControllerActionEventInterface
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private ActionController|CompanyController|ManagementController|MapController $controller,
        private ?Company $company,
        private array $settings,
        private RequestInterface $request,
    ) {}

    public function getController(): ActionController
    {
        return $this->controller;
    }

    public function getCompanyController(): CompanyController
    {
        return $this->controller;
    }

    /**
     * Since 9.0.0 the "create", "update" and "activate" actions are
     * located in ManagementController.
     */
    public function getManagementController(): ManagementController
    {
        if (!$this->controller instanceof ManagementController) {
            throw new \UnexpectedValueException(
                'Event was not dispatched by ManagementController, but by ' . $this->controller::class,
                1793779200,
            );
        }

        return $this->controller;
    }

    public function getMapController(): MapController
    {
        return $this->controller;
    }

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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
