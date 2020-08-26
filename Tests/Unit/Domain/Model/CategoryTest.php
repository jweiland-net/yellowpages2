<?php

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Domain\Model;

use JWeiland\Yellowpages2\Domain\Model\Category;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Stefan Froemken <projects@jweiland.net>
 */
class CategoryTest extends UnitTestCase
{
    /**
     * @var Category
     */
    protected $subject;

    /**
     * set up.
     */
    public function setUp()
    {
        $this->subject = new Category();
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
    public function getIconInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getIcon()
        );
    }

    /**
     * @test
     */
    public function setIconSetsIcon()
    {
        $this->subject->setIcon('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getIcon()
        );
    }

    /**
     * @test
     */
    public function setIconWithIntegerResultsInString()
    {
        $this->subject->setIcon(123);
        self::assertSame('123', $this->subject->getIcon());
    }

    /**
     * @test
     */
    public function setIconWithBooleanResultsInString()
    {
        $this->subject->setIcon(true);
        self::assertSame('1', $this->subject->getIcon());
    }
}
