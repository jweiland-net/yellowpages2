<?php

namespace JWeiland\Yellowpages2\Tests\Unit\Domain\Model;

/*
 * This file is part of the yellowpages2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
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
    public function getIconInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getIcon()
        );
    }

    /**
     * @test
     */
    public function setIconSetsIcon() {
        $this->subject->setIcon('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getIcon()
        );
    }

    /**
     * @test
     */
    public function setIconWithIntegerResultsInString() {
        $this->subject->setIcon(123);
        $this->assertSame('123', $this->subject->getIcon());
    }

    /**
     * @test
     */
    public function setIconWithBooleanResultsInString() {
        $this->subject->setIcon(true);
        $this->assertSame('1', $this->subject->getIcon());
    }
}
