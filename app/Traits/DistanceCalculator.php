<?php

namespace App\Traits;

trait DistanceCalculator
{
    

    // Define a method to calculate the distance between two points
    public function getDistance($lat1, $long1, $lat2, $long2)
    {
       
        // Define a constant for the radius of the earth in kilometers
        $earth_radius = 6371;

        // Convert the coordinates to radians
        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($long1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($long2);

        // Calculate the difference between the coordinates
        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;

        // Apply the Haversine formula
        $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlng / 2) ** 2;
        $c = 2 * asin(sqrt($a));
        $d = $earth_radius * $c;

        // Return the distance in kilometers
        return $d;
    }
}