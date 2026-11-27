<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Domain\Repository;

use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Repository\CompanyRepository;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class CompanyRepositoryTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/maps2',
        'jweiland/yellowpages2',
    ];

    private CompanyRepository $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/CompanyForm.csv');

        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->subject = $this->get(CompanyRepository::class);
        $this->subject->setDefaultQuerySettings($querySettings);
    }

    #[Test]
    public function findByFeUserReturnsCompaniesOfUserOnly(): void
    {
        self::assertSame(['Bakery Müller'], $this->getCompanyNames($this->subject->findByFeUser(5)));
    }

    /**
     * Companies without owner (fe_user = 0) must never be returned for visitors without login
     */
    #[Test]
    public function findByFeUserWithoutUserReturnsNothing(): void
    {
        $this->getConnectionPool()
            ->getConnectionForTable('tx_yellowpages2_domain_model_company')
            ->update('tx_yellowpages2_domain_model_company', ['fe_user' => 0], ['uid' => 1]);

        self::assertSame([], $this->getCompanyNames($this->subject->findByFeUser(0)));
    }

    #[Test]
    public function searchCompaniesFindsCompanyByName(): void
    {
        self::assertSame(['Bakery Müller'], $this->getCompanyNames($this->subject->searchCompanies('Bakery', 0, [])));
    }

    #[Test]
    public function searchCompaniesEscapesWildcardsOfVisitor(): void
    {
        self::assertSame([], $this->getCompanyNames($this->subject->searchCompanies('%', 0, [])));
        self::assertSame([], $this->getCompanyNames($this->subject->searchCompanies('B_kery', 0, [])));
    }

    /**
     * @param iterable<Company> $companies
     * @return list<string>
     */
    private function getCompanyNames(iterable $companies): array
    {
        $names = [];
        foreach ($companies as $company) {
            $names[] = $company->getCompany();
        }

        return $names;
    }
}
