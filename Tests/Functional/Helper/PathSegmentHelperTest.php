<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Helper;

use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Helper\PathSegmentHelper;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class PathSegmentHelperTest extends FunctionalTestCase
{
    private const TABLE = 'tx_yellowpages2_domain_model_company';

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
        'jweiland/yellowpages2',
    ];

    protected PathSegmentHelper $subject;

    protected array $originalPathSegmentConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(PathSegmentHelper::class);
        $this->originalPathSegmentConfig = $GLOBALS['TCA'][self::TABLE]['columns']['path_segment']['config'];
    }

    protected function tearDown(): void
    {
        $GLOBALS['TCA'][self::TABLE]['columns']['path_segment']['config'] = $this->originalPathSegmentConfig;

        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    #[Test]
    public function updatePathSegmentForCompanyGeneratesCleanSlugForFirstCompany(): void
    {
        $company = new Company();
        $company->setCompany('Hello World');

        $this->subject->updatePathSegmentForCompany($company);

        self::assertSame('hello-world', $company->getPathSegment());
    }

    #[Test]
    public function updatePathSegmentForCompanyAppendsIncrementOnDuplicateSlug(): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::TABLE)->insert(
            self::TABLE,
            [
                'uid' => 1,
                'pid' => 1,
                'company' => 'Hello World',
                'path_segment' => 'hello-world',
            ],
        );

        $company = new Company();
        $company->setCompany('Hello World');

        $this->subject->updatePathSegmentForCompany($company);

        self::assertSame('hello-world-1', $company->getPathSegment());
    }

    #[Test]
    public function updatePathSegmentForCompanyPersistsCompanyEarlyIfUidIsPartOfGeneratorFields(): void
    {
        $GLOBALS['TCA'][self::TABLE]['columns']['path_segment']['config']['generatorOptions']['fields'] = ['company', 'uid'];

        $company = new Company();
        $company->setCompany('Hello World');

        self::assertNull($company->getUid());

        $this->subject->updatePathSegmentForCompany($company);

        self::assertNotNull($company->getUid());
        self::assertStringEndsWith('-' . $company->getUid(), $company->getPathSegment());
    }

    #[Test]
    public function updatePathSegmentForCompanyPersistsCompanyEarlyIfEvalIsUniqueInPid(): void
    {
        $GLOBALS['TCA'][self::TABLE]['columns']['path_segment']['config']['eval'] = 'uniqueInPid';

        $company = new Company();
        $company->setCompany('Hello World');

        self::assertNull($company->getUid());

        $this->subject->updatePathSegmentForCompany($company);

        self::assertNotNull($company->getUid());
        self::assertSame('hello-world', $company->getPathSegment());
    }
}
