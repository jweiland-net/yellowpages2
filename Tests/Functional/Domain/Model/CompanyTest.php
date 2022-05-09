<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Functional\Domain\Model;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Yellowpages2\Domain\Model\Company;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case.
 */
class CompanyTest extends FunctionalTestCase
{
    /**
     * @var Company
     */
    protected $subject;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2',
    ];

    public function setUp(): void
    {
        parent::setup();

        $this->subject = new Company();
    }

    public function tearDown(): void
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

    /**
     * @test
     */
    public function setTxMaps2UidSetsTxMaps2Uid(): void
    {
        $instance = new PoiCollection();
        $this->subject->setTxMaps2Uid($instance);

        self::assertSame(
            $instance,
            $this->subject->getTxMaps2Uid()
        );
    }
}
