<?php

namespace App\Services;

use App\Models\City;
use App\Models\User;
use InvalidArgumentException;

class CityService
{
    public function getAllCities()
    {
        $cities = City::all();

        if (!$cities) {
            throw new InvalidArgumentException('There Is No Cities Available');
        }

        return $cities;
    }



    public function getCity(int $city_id)
    {
        $city = City::findOrFail($city_id);


        return $city;
    }

    public function createCity(array $data): City
    {
        $city = City::create(
            ['name' => [
                'en' => $data['name_en'],
                'ar' => $data['name_ar'],
            ],]
        );

        if (!$city) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $city;
    }

    public function updateCity(array $data): City
    {
        $city = City::findOrFail($data['id']);
        if(isset($data['name_ar'])){
            $city->update([
                'name' => [
                    'ar'=> $data['name_ar']
                ]
                ]);
        }

        if(isset($data['name_en'])){
            $city->update([
                'name' => [
                    'en'=> $data['name_en']
                ]
                ]);
        }

 
        return $city;
    }

    public function show(int $city_id): City
    {
        $city = City::findOrFail($city_id);

        return $city;
    }

    public function delete(int $city_id): void
    {
        $city = City::findOrFail($city_id);

        $city->delete();
    }

    public function forceDelete(int $city_id): void
    {
        $city = City::findOrFail($city_id);

        $city->forceDelete();
    }
}
