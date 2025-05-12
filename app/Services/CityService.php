<?php

namespace App\Services;

use App\Models\City;
use App\Models\User;
use InvalidArgumentException;

class CityService
{
    public function getAllCities()//si
    {
        $cities = City::all();

        if (!$cities) {
            throw new InvalidArgumentException('There Is No Cities Available');
        }

        return $cities;
    }

    public function show(int $city_id): City
    {
        $city = City::findOrFail($city_id);

        return $city;
    }

}
