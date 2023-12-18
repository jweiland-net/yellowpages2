<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Domain\Model;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Yellowpages2\Domain\Model\Company;

/**
 * Test case.
 */
class CompanyTest extends FunctionalTestCase
{
    protected Company $subject;

    protected function setUp(): void
    {
        parent::setup();

        $this->subject = new Company();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getTxMaps2UidInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getTxMaps2Uid());
    }
}
