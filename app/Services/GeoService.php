<?php

namespace App\Services;

class GeoService
{
    /**
     * Earth radius in meters
     */
    private const EARTH_RADIUS_METERS = 6371000;

    /**
     * Calculate the distance between two GPS coordinates using the Haversine formula.
     *
     * @param float $latitudeFrom Latitude of point 1 in degrees
     * @param float $longitudeFrom Longitude of point 1 in degrees
     * @param float $latitudeTo Latitude of point 2 in degrees
     * @param float $longitudeTo Longitude of point 2 in degrees
     * @return int Distance in meters (rounded integer)
     */
    public static function calculateDistance(
        float $latitudeFrom,
        float $longitudeFrom,
        float $latitudeTo,
        float $longitudeTo
    ): int {
        // Convert coordinates from degrees to radians
        $latFromRad = deg2rad($latitudeFrom);
        $lonFromRad = deg2rad($longitudeFrom);
        $latToRad   = deg2rad($latitudeTo);
        $lonToRad   = deg2rad($longitudeTo);

        // Differences in coordinates
        $latDelta = $latToRad - $latFromRad;
        $lonDelta = $lonToRad - $lonFromRad;

        // Haversine formula component: a = sin²(Δlat/2) + cos(lat1) * cos(lat2) * sin²(Δlon/2)
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFromRad) * cos($latToRad) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        // Angular distance in radians: c = 2 * atan2(√a, √(1-a))
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Distance in meters: d = R * c
        $distance = self::EARTH_RADIUS_METERS * $c;

        return (int) round($distance);
    }
}
