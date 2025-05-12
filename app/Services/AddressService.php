<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;
use InvalidArgumentException;

class AddressService
{
    public function getAddressByUserid(int $user_id) //si
    {
        $user = User::where('id', $user_id)->with('addresses')->firstOrFail();
        $addresses = $user->addresses;
        if (!$addresses) {
            throw new InvalidArgumentException('User Have no addresses');
        }

        return $addresses;
    }

    public function createAddress(array $data, $user, $city_id, $city) //si
    {
        $data['user_id'] = $user->id;
        $address = Address::create([
            'user_id' => $data['user_id'],
            'branch_id' => $data['branch_id'] ?? null,
            'first_name' => $data['first_name'] ?? $user->first_name,
            'father_name' => $data['father_name'] ?? null,
            'last_name' => $data['last_name'] ?? $user->last_name,
            'phone' => $data['phone'] ?? $user->phone,
            'city' => $city->name,
            'street' => $data['street'],
            'isKadmous' => (bool)$data['isKadmous'],
            'another_details' => $data['another_details'] ?? null,
            'neighborhood' => $data['neighborhood'] ?? null,
            'lat_long' => $data['lat_long'] ?? null,
            'phone_number_two' => $data['phone_number_two'] ?? null,
            'city_id' => $city_id
        ]);
        if (!$address) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $address;
    }

    public function updateAddress(array $data, int $user_id): Address //si
    {
        $address = Address::findOrFail($data['id']);
        $data['user_id'] = $user_id;
        $address->update($data);
        if (!$address) {
            throw new InvalidArgumentException('There Is No Addresss Available');
        }

        return $address;
    }

    public function delete(int $address_id): void //si
    {
        $address = Address::where('user_id', auth('sanctum')->user()->id)->find($address_id);
        if (!$address) {
            throw new \Exception('Address not found', 400);
        }

        $address->delete();
    }
}
