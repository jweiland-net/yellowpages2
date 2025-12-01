<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Domain\Model;

use JWeiland\Yellowpages2\Domain\Model\District;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
class DistrictTest extends UnitTestCase
{
    protected District $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new District();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    #[Test]
    public function getDistrictInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getDistrict(),
        );
    }

    #[Test]
    public function setDistrictSetsDistrict(): void
    {
        $this->subject->setDistrict('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDistrict(),
        );
    }
}
