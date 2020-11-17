<?php

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Domain\Model;

use JWeiland\Yellowpages2\Domain\Model\District;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 */
class DistrictTest extends UnitTestCase
{
    /**
     * @var District
     */
    protected $subject;

    /**
     * set up.
     */
    public function setUp()
    {
        $this->subject = new District();
    }

    /**
     * tear down.
     */
    public function tearDown()
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getDistrictInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getDistrict()
        );
    }

    /**
     * @test
     */
    public function setDistrictSetsDistrict()
    {
        $this->subject->setDistrict('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDistrict()
        );
    }

    /**
     * @test
     */
    public function setDistrictWithIntegerResultsInString()
    {
        $this->subject->setDistrict(123);
        self::assertSame('123', $this->subject->getDistrict());
    }

    /**
     * @test
     */
    public function setDistrictWithBooleanResultsInString()
    {
        $this->subject->setDistrict(true);
        self::assertSame('1', $this->subject->getDistrict());
    }
}
