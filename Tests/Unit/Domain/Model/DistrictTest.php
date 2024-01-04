<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Domain\Model;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use JWeiland\Yellowpages2\Domain\Model\District;

/**
 * Test case.
 */
class DistrictTest extends UnitTestCase
{
    protected District $subject;

    protected function setUp(): void
    {
        $this->subject = new District();
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
    public function getDistrictInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getDistrict()
        );
    }

    /**
     * @test
     */
    public function setDistrictSetsDistrict(): void
    {
        $this->subject->setDistrict('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDistrict()
        );
    }
}
