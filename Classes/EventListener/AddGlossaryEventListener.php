<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\EventListener;

use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use JWeiland\Yellowpages2\Event\PostProcessFluidVariablesEvent;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Add a glossary (A-Z list) on top of the company list view
 */
class AddGlossaryEventListener extends AbstractControllerEventListener
{
    protected GlossaryService $glossaryService;

    protected CompanyRepository $companyRepository;

    protected array $allowedControllerActions = [
        'Company' => [
            'list',
            'search',
        ],
    ];

    public function __construct(GlossaryService $glossaryService, CompanyRepository $companyRepository)
    {
        $this->glossaryService = $glossaryService;
        $this->companyRepository = $companyRepository;
    }

    public function __invoke(PostProcessFluidVariablesEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            $event->addFluidVariable(
                'glossar',
                $this->glossaryService->buildGlossary(
                    $this->companyRepository->getExtbaseQueryToFindAllEntries(),
                    $this->getOptions($event),
                    $event->getRequest(),
                ),
            );
        }
    }

    protected function getOptions(PostProcessFluidVariablesEvent $event): array
    {
        $options = [
            'extensionName' => 'yellowpages2',
            'pluginName' => 'directory',
            'controllerName' => 'Company',
            'column' => 'company',
            'settings' => $event->getSettings(),
        ];

        if (
            isset($event->getSettings()['glossary'])
            && is_array($event->getSettings()['glossary'])
        ) {
            ArrayUtility::mergeRecursiveWithOverrule($options, $event->getSettings()['glossary']);
        }

        return $options;
    }
}
