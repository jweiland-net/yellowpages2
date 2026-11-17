<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Finisher;

use JWeiland\Yellowpages2\Form\CompanyFormData;
use JWeiland\Yellowpages2\Form\CompanyFormDataMapper;
use JWeiland\Yellowpages2\Service\CompanyAccessService;
use JWeiland\Yellowpages2\Service\CompanyRecordService;
use JWeiland\Yellowpages2\Service\PluginSettingsService;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;

/**
 * Stores the values of the company form based on EXT:form as company
 * record incl. its category relations and file references.
 *
 * Options:
 *
 * - "elements": Maps the form elements on the columns of the company table.
 *   See CompanyFormDataMapper.
 * - "databaseColumnMappings": Static values of columns like "pid" or
 *   "hidden". "{__currentTimestamp}" is replaced by the current time.
 *   Without "pid" new companies are stored in the first storage page of
 *   the plugin or else on the current page.
 *
 * The company to edit is taken from the URI of the form. The rules of
 * CompanyAccessService are checked again before anything is stored, as the
 * form definition could be used in other places, too. The UID of the stored
 * company is available as finisher variable "SaveCompany.companyUid".
 */
#[Autoconfigure(public: true, shared: false)]
class SaveCompanyFinisher extends AbstractFinisher
{
    public function __construct(
        protected readonly CompanyAccessService $companyAccessService,
        protected readonly CompanyFormDataMapper $companyFormDataMapper,
        protected readonly CompanyRecordService $companyRecordService,
        protected readonly PluginSettingsService $pluginSettingsService,
    ) {}

    /**
     * @throws FinisherException
     */
    protected function executeInternal(): ?string
    {
        $request = $this->finisherContext->getRequest();
        $this->throwExceptionIfAccessIsDenied($this->companyAccessService->getDenialReason($request));

        $companyUid = $this->companyAccessService->getCompanyUid($request);
        $companyUid = $this->companyRecordService->save(
            $this->getCompanyFormData($companyUid),
            $companyUid,
            $this->companyAccessService->getFrontendUserUid(),
        );

        $this->finisherContext->getFinisherVariableProvider()->add(
            $this->shortFinisherIdentifier,
            'companyUid',
            $companyUid,
        );

        return null;
    }

    /**
     * @throws FinisherException
     */
    protected function throwExceptionIfAccessIsDenied(string $denialReason): void
    {
        if ($denialReason === '') {
            return;
        }

        $this->finisherContext->cancel();

        throw new FinisherException(
            'The current user is not allowed to create or edit this company in frontend: ' . $denialReason,
            1763380801,
        );
    }

    protected function getCompanyFormData(int $companyUid): CompanyFormData
    {
        $columns = $this->getDatabaseColumnMappings();
        $elements = $this->parseOption('elements');

        return $this->companyFormDataMapper->map(
            $this->finisherContext->getFormRuntime()->getFormDefinition(),
            $this->finisherContext->getFormValues(),
            is_array($elements) ? $elements : [],
            $companyUid,
            (int)($columns['pid'] ?? 0),
        )->withAdditionalColumns($columns);
    }

    /**
     * @return array<string, int|string>
     */
    protected function getDatabaseColumnMappings(): array
    {
        $mappings = $this->options['databaseColumnMappings'] ?? [];
        $columns = [];
        foreach (is_array($mappings) ? $mappings : [] as $column => $_) {
            $value = $this->parseOption('databaseColumnMappings.' . $column . '.value');
            $columns[(string)$column] = is_int($value) ? $value : (is_scalar($value) ? (string)$value : '');
        }

        $columns['pid'] = (int)($columns['pid'] ?? 0) > 0 ? (int)$columns['pid'] : $this->getDefaultPid();

        return $columns;
    }

    protected function getDefaultPid(): int
    {
        $request = $this->finisherContext->getRequest();
        $storagePids = $this->pluginSettingsService->getStoragePids($request);
        $pageArguments = $request->getAttribute('routing');

        return $storagePids[0] ?? ($pageArguments instanceof PageArguments ? $pageArguments->getPageId() : 0);
    }
}
