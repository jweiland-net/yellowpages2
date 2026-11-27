<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Domain\Finisher;

use JWeiland\Yellowpages2\Domain\Finisher\SaveCompanyFinisher;
use JWeiland\Yellowpages2\Service\ModerationLinkTokenService;
use JWeiland\Yellowpages2\Tests\Functional\Traits\CompanyFormDefinitionTrait;
use JWeiland\Yellowpages2\Tests\Functional\Traits\CompanyFormRequestTrait;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Form\Domain\Finishers\FinisherContext;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Domain\Runtime\FormState;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
final class SaveCompanyFinisherTest extends FunctionalTestCase
{
    use CompanyFormDefinitionTrait;
    use CompanyFormRequestTrait;

    private const TABLE = 'tx_yellowpages2_domain_model_company';

    private const FORM_VALUES = [
        'company' => 'Flowers & More',
        'street' => 'Echterdinger Straße',
        'house-number' => '59',
        'zip' => '70794',
        'city' => 'Filderstadt',
        'district' => '2',
        'description' => "First line\nsecond line & more\n\nParagraph",
        'opening-times' => 'Mo-Fr <b>8-18</b>',
        'barrier-free' => '1',
        'main-trade' => '4',
        'trades' => ['2', '3', '99'],
        'position' => ['latitude' => '48.6861', 'longitude' => '9.2159'],
    ];

    protected array $coreExtensionsToLoad = [
        'form',
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/maps2',
        'jweiland/yellowpages2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/CompanyForm.csv');
    }

    #[Test]
    public function executeStoresNewCompanyOfFrontendUser(): void
    {
        $this->loginFrontendUser(6);

        $companyUid = $this->executeFinisher(self::FORM_VALUES, $this->buildFormRequest());
        $company = $this->getRecord(self::TABLE, $companyUid);

        self::assertSame(10, (int)$company['pid']);
        self::assertSame(1, (int)$company['hidden']);
        self::assertSame(6, (int)$company['fe_user']);
        self::assertSame('Flowers & More', $company['company']);
        self::assertSame(2, (int)$company['district']);
        self::assertSame(1, (int)$company['barrier_free']);
        self::assertSame("<p>First line<br />second line &amp; more</p>\r\n<p>Paragraph</p>", $company['description']);
        self::assertSame('Mo-Fr 8-18', $company['opening_times']);
        self::assertStringStartsWith('flowers-more-', $company['path_segment']);
    }

    #[Test]
    public function executeStoresAllowedCategoriesOfNewCompany(): void
    {
        $this->loginFrontendUser(6);

        $companyUid = $this->executeFinisher(self::FORM_VALUES, $this->buildFormRequest());
        $company = $this->getRecord(self::TABLE, $companyUid);

        self::assertSame(1, (int)$company['main_trade']);
        self::assertSame(2, (int)$company['trades']);
        self::assertSame([4], $this->getCategoryUids($companyUid, 'main_trade'));
        self::assertSame([2, 3], $this->getCategoryUids($companyUid, 'trades'));
    }

    #[Test]
    public function executeStoresCorrectedPositionInNewPoiCollection(): void
    {
        $this->loginFrontendUser(6);

        $companyUid = $this->executeFinisher(self::FORM_VALUES, $this->buildFormRequest());
        $poiCollection = $this->getRecord(
            'tx_maps2_domain_model_poicollection',
            (int)$this->getRecord(self::TABLE, $companyUid)['tx_maps2_uid'],
        );

        self::assertSame('Point', $poiCollection['collection_type']);
        self::assertSame('Flowers & More', $poiCollection['title']);
        self::assertSame('Echterdinger Straße 59, 70794 Filderstadt', $poiCollection['address']);
        self::assertEqualsWithDelta(48.6861, (float)$poiCollection['latitude'], 0.000001);
        self::assertEqualsWithDelta(9.2159, (float)$poiCollection['longitude'], 0.000001);
        self::assertSame(10, (int)$poiCollection['pid']);
    }

    #[Test]
    public function executeProvidesModerationTokenOfStoredCompany(): void
    {
        $this->loginFrontendUser(6);

        $finisherContext = null;
        $companyUid = $this->executeFinisher(self::FORM_VALUES, $this->buildFormRequest(), $finisherContext);

        self::assertSame(
            $this->get(ModerationLinkTokenService::class)->generateToken($companyUid),
            $finisherContext?->getFinisherVariableProvider()->get('SaveCompany', 'moderationToken'),
        );
    }

    #[Test]
    public function executeUpdatesPoiCollectionOfEditedCompany(): void
    {
        $this->executeFinisher(self::FORM_VALUES, $this->buildModerationRequest(1));

        $company = $this->getRecord(self::TABLE, 1);
        $poiCollection = $this->getRecord('tx_maps2_domain_model_poicollection', 1);

        self::assertSame(1, (int)$company['tx_maps2_uid']);
        self::assertSame('Flowers & More', $poiCollection['title']);
        self::assertEqualsWithDelta(48.6861, (float)$poiCollection['latitude'], 0.000001);
        self::assertSame(
            1,
            $this->get(ConnectionPool::class)->getConnectionForTable('tx_maps2_domain_model_poicollection')
                ->count('*', 'tx_maps2_domain_model_poicollection', []),
        );
    }

    #[Test]
    public function executeKeepsOwnerPidAndPathSegmentOfEditedCompany(): void
    {
        $this->executeFinisher(self::FORM_VALUES, $this->buildModerationRequest(1));

        $company = $this->getRecord(self::TABLE, 1);

        self::assertSame(5, (int)$company['fe_user']);
        self::assertSame(10, (int)$company['pid']);
        self::assertSame('bakery-mueller-1', $company['path_segment']);
        self::assertSame(1, (int)$company['hidden']);
        self::assertSame([4], $this->getCategoryUids(1, 'main_trade'));
    }

    #[Test]
    public function executeWithInvalidPositionKeepsPoiCollection(): void
    {
        $this->executeFinisher(['position' => ['latitude' => 'x', 'longitude' => '']] + self::FORM_VALUES, $this->buildModerationRequest(1));

        self::assertEqualsWithDelta(48.6853, (float)$this->getRecord('tx_maps2_domain_model_poicollection', 1)['latitude'], 0.000001);
    }

    /**
     * Since TYPO3 14 EXT:form catches the FinisherException, cancels the finisher context
     * and renders the error message instead (see Feature-107581).
     */
    #[Test]
    public function executeForForeignCompanyCancelsFinisherContext(): void
    {
        $this->loginFrontendUser(6);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with(
            self::stringContains('without permission'),
            [
                'companyUid' => 1,
                'feUserUid' => 6,
                'denialReason' => 'unauthorizedCompanyUser',
                'invalidModerationToken' => false,
            ],
        );

        $this->executeFinisher(self::FORM_VALUES, $this->buildFormRequest(['company' => '1']), $finisherContext, $logger);

        self::assertTrue($finisherContext?->isCancelled());
        self::assertNull($finisherContext->getFinisherVariableProvider()->get('SaveCompany', 'companyUid'));
    }

    #[Test]
    public function executeWithoutLoginCancelsFinisherContext(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with(
            self::stringContains('without permission'),
            self::callback(static fn(array $context): bool => $context['denialReason'] === 'pluginNeedsLogin'
                && $context['feUserUid'] === 0
                && !array_key_exists('email', $context)),
        );

        $this->executeFinisher(self::FORM_VALUES, $this->buildFormRequest(), $finisherContext, $logger);

        self::assertTrue($finisherContext?->isCancelled());
        self::assertNull($finisherContext->getFinisherVariableProvider()->get('SaveCompany', 'companyUid'));
    }

    /**
     * @param array<string, mixed> $formValues
     * @return int UID of the stored company
     */
    private function executeFinisher(
        array $formValues,
        Request $request,
        ?FinisherContext &$finisherContext = null,
        ?LoggerInterface $logger = null,
    ): int {
        $formState = new FormState();
        foreach ($formValues as $identifier => $value) {
            $formState->setFormValue($identifier, $value);
        }

        $formRuntime = $this->createMock(FormRuntime::class);
        $formRuntime->method('getFormDefinition')->willReturn($this->buildCompanyFormDefinition());
        $formRuntime->method('getFormState')->willReturn($formState);
        $formRuntime->method('getRequest')->willReturn($request);
        $formRuntime->method('getRenderingOptions')->willReturn([
            'translation' => ['translationFiles' => []],
            // Needed to render "Finishers/Error", if the finisher fails
            'templateRootPaths' => [10 => 'EXT:form/Resources/Private/Frontend/Templates/'],
        ]);

        $finisherContext = new FinisherContext($formRuntime, $request);
        $finisher = $this->get(SaveCompanyFinisher::class);
        $finisher->setFinisherIdentifier('SaveCompanyFinisher');
        if ($logger instanceof LoggerInterface) {
            $finisher->setLogger($logger);
        }
        $finisher->setOptions([
            'table' => self::TABLE,
            'elements' => $this->getElementsConfiguration(),
            'databaseColumnMappings' => ['pid' => ['value' => '0'], 'hidden' => ['value' => '1']],
        ]);
        $finisher->execute($finisherContext);

        return (int)$finisherContext->getFinisherVariableProvider()->get('SaveCompany', 'companyUid');
    }

    /**
     * @return array<string, mixed>
     */
    private function getRecord(string $table, int $uid): array
    {
        $queryBuilder = $this->get(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $record = $queryBuilder
            ->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        return is_array($record) ? $record : [];
    }

    /**
     * @return int[]
     */
    private function getCategoryUids(int $companyUid, string $column): array
    {
        $rows = $this->get(ConnectionPool::class)->getConnectionForTable('sys_category_record_mm')
            ->select(['uid_local'], 'sys_category_record_mm', ['uid_foreign' => $companyUid, 'fieldname' => $column], [], ['sorting_foreign' => 'ASC'])
            ->fetchFirstColumn();

        return array_map(intval(...), $rows);
    }
}
