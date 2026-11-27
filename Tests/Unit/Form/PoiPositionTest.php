<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Form;

use JWeiland\Yellowpages2\Form\PoiPosition;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
final class PoiPositionTest extends UnitTestCase
{
    #[Test]
    public function fromFormValueReturnsPosition(): void
    {
        $position = PoiPosition::fromFormValue(['latitude' => '48.6853', 'longitude' => '9.2148']);

        self::assertInstanceOf(PoiPosition::class, $position);
        self::assertSame(48.6853, $position->latitude);
        self::assertSame(9.2148, $position->longitude);
    }

    public static function invalidFormValueDataProvider(): array
    {
        return [
            'null' => [null],
            'string' => ['48.6,9.2'],
            'missing longitude' => [['latitude' => '48.6']],
            'not numeric' => [['latitude' => 'north', 'longitude' => '9.2']],
            'latitude out of range' => [['latitude' => '91', 'longitude' => '9.2']],
            'longitude out of range' => [['latitude' => '48.6', 'longitude' => '-181']],
            'zero position' => [['latitude' => '0', 'longitude' => '0']],
        ];
    }

    #[Test]
    #[DataProvider('invalidFormValueDataProvider')]
    public function fromFormValueWithInvalidValueReturnsNull(mixed $value): void
    {
        self::assertNull(PoiPosition::fromFormValue($value));
    }

    #[Test]
    public function toFormValueReturnsStrings(): void
    {
        self::assertSame(
            ['latitude' => '48.6853', 'longitude' => '9.2148'],
            (new PoiPosition(48.6853, 9.2148))->toFormValue(),
        );
    }
}
