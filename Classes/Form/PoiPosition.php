<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Form;

/**
 * The position of a company on the map (POI collection of EXT:maps2)
 */
final readonly class PoiPosition
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    /**
     * Creates the position from the value of the form element
     * "Maps2PoiPosition" ("latitude" and "longitude" as strings). Returns
     * null for missing or invalid coordinates.
     */
    public static function fromFormValue(mixed $value): ?self
    {
        $latitude = is_array($value) ? $value['latitude'] ?? null : null;
        $longitude = is_array($value) ? $value['longitude'] ?? null : null;
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return null;
        }

        $position = new self((float)$latitude, (float)$longitude);

        return $position->isValid() ? $position : null;
    }

    /**
     * @return array{latitude: string, longitude: string}
     */
    public function toFormValue(): array
    {
        return [
            'latitude' => (string)$this->latitude,
            'longitude' => (string)$this->longitude,
        ];
    }

    private function isValid(): bool
    {
        return abs($this->latitude) <= 90
            && abs($this->longitude) <= 180
            && ($this->latitude !== 0.0 || $this->longitude !== 0.0);
    }
}
