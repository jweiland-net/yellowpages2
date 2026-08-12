<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Helper;

use JWeiland\Yellowpages2\Domain\Model\Company;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/*
 * Helper class to generate a path segment (slug) for a company record.
 * Used while executing the UpgradeWizard and saving records in the frontend.
 */
readonly class PathSegmentHelper
{
    private const TABLE_NAME = 'tx_yellowpages2_domain_model_company';

    private const FIELD_NAME = 'path_segment';

    public function __construct(
        protected PersistenceManagerInterface $persistenceManager,
    ) {}

    /**
     * @throws SiteNotFoundException
     */
    public function generatePathSegment(array $baseRecord, int $pid): string
    {
        $slugHelper = $this->getSlugHelper();
        $slug = $slugHelper->generate($baseRecord, $pid);

        $recordState = RecordStateFactory::forName(self::TABLE_NAME)
            ->fromArray($baseRecord, $pid, (int)($baseRecord['uid'] ?? 0));

        // Mirror TYPO3 core's own dispatching in DataHandler::checkValueForSlug(), as a 3rd party
        // extension could override "eval" of path_segment to a different uniqueness scope.
        $evalCodes = $this->getEvalCodes();
        if (in_array('unique', $evalCodes, true)) {
            $slug = $slugHelper->buildSlugForUniqueInTable($slug, $recordState);
        }
        if (in_array('uniqueInSite', $evalCodes, true)) {
            $slug = $slugHelper->buildSlugForUniqueInSite($slug, $recordState);
        }
        if (in_array('uniqueInPid', $evalCodes, true)) {
            $slug = $slugHelper->buildSlugForUniqueInPid($slug, $recordState);
        }

        return $slug;
    }

    /**
     * @throws SiteNotFoundException
     */
    public function updatePathSegmentForCompany(Company $company): void
    {
        // A 3rd party extension could override the TCA of path_segment (e.g. via
        // Configuration/TCA/Overrides/) to add "uid" to generatorOptions.fields, or to switch
        // "eval" to "uniqueInPid"/"uniqueInSite". All three cases need a real, already assigned
        // uid/pid, which an Extbase object only gets once it has been added and persisted.
        // Persisting the company here is safe even though createAction() persists it again later:
        // Extbase skips the insert on that second run, as DomainObject::_isNew() is already false,
        // and only writes properties changed afterwards (e.g. path_segment).
        if (
            !$company->getUid()
            && (
                in_array('uid', $this->getGeneratorFields(), true)
                || array_intersect(['uniqueInPid', 'uniqueInSite'], $this->getEvalCodes()) !== []
            )
        ) {
            $this->persistenceManager->add($company);
            $this->persistenceManager->persistAll();
        }

        $company->setPathSegment(
            $this->generatePathSegment(
                $company->getBaseRecordForPathSegment(),
                $company->getPid() ?? 0,
            ),
        );
    }

    protected function getSlugHelper(): SlugHelper
    {
        return GeneralUtility::makeInstance(
            SlugHelper::class,
            self::TABLE_NAME,
            self::FIELD_NAME,
            $this->getFieldConfiguration(),
        );
    }

    protected function getGeneratorFields(): array
    {
        return (array)($this->getFieldConfiguration()['generatorOptions']['fields'] ?? []);
    }

    protected function getEvalCodes(): array
    {
        return GeneralUtility::trimExplode(',', (string)($this->getFieldConfiguration()['eval'] ?? ''), true);
    }

    protected function getFieldConfiguration(): array
    {
        return (array)($GLOBALS['TCA'][self::TABLE_NAME]['columns'][self::FIELD_NAME]['config'] ?? []);
    }
}
