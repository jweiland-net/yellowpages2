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
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/*
 * Helper class to generate a path segment (slug) for a company record.
 * Used while executing the UpgradeWizard and saving records in frontend.
 */
class PathSegmentHelper
{
    /**
     * @var SlugHelper
     */
    protected $slugHelper;

    public function __construct(SlugHelper $slugHelper = null)
    {
        if ($slugHelper === null) {
            // Add uid to slug, to prevent duplicates
            $config = $GLOBALS['TCA']['tx_yellowpages2_domain_model_company']['columns']['path_segment']['config'];
            $config['generatorOptions']['fields'] = ['company', 'uid'];

            $slugHelper = GeneralUtility::makeInstance(
                SlugHelper::class,
                'tx_yellowpages2_domain_model_company',
                'path_segment',
                $config
            );
        }
        $this->slugHelper = $slugHelper;
    }

    public function generatePathSegment(
        array $baseRecord,
        int $pid
    ): string {
        return $this->slugHelper->generate(
            $baseRecord,
            $pid
        );
    }

    public function updatePathSegmentForCompany(Company $company): void
    {
        // First of all, we have to check, if an UID is available
        if (!$company->getUid()) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $persistenceManager = $objectManager->get(PersistenceManagerInterface::class);
            $persistenceManager->persistAll();
        }

        $company->setPathSegment(
            $this->generatePathSegment(
                $company->getBaseRecordForPathSegment(),
                $company->getPid()
            )
        );
    }
}
