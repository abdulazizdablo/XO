<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class AddressService
{
    public function getAllAddresss()
    {
        $addresss = Address::paginate(10);

        if (!$addresss) {
            throw new InvalidArgumentException('There Is No Addresss Available');
        }

        return $addresss;
    }

    public function getAddressByUserid(int $user_id)
    {
        $user = User::where('id', $user_id)->with('addresses')->firstOrFail();
        $addresses=$user->addresses;
        if (!$addresses) {
            throw new InvalidArgumentException('User Have no addresses');
        }

        return $addresses;
    }

    public function getAddress($address_id)
    {
		$user = auth('sanctum')->user();
        
		if(!$user){
           return response()->error('Unauthorized',403);
        }
		
        $address = Address::where('user_id',$user->id)->findOrFail($address_id);

        if (!$address) {
            throw new InvalidArgumentException('address not found');
        }

        return $address;
    }

    public function createAddress(array $data, $user, $city_id, $city)
    {
        $data['user_id'] = $user->id;
        // $address = Address::create($data);

        $address = Address::create([
            'user_id' => $data['user_id'],
			'branch_id' => $data['branch_id'] ?? null,
            'first_name'=>$data['first_name']??$user->first_name,
            'father_name'=>$data['father_name']??null,
            'last_name'=>$data['last_name']??$user->last_name,
            'phone'=>$data['phone']??$user->phone,
            'city' => $city->name,
            'street' => $data['street'],
			'isKadmous' => (bool)$data['isKadmous'],
            'another_details' => $data['another_details']??null,
            'neighborhood' => $data['neighborhood']??null,
            'lat_long' => $data['lat_long']??null,
            'phone_number_two' => $data['phone_number_two']??null,
            'city_id'=>$city_id
        ]);
        if (!$address) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $address;
    }

    public function updateAddress(array $data, int $user_id): Address
    {
        $address = Address::findOrFail($data['id']);
        $data['user_id'] = $user_id;
        $address->update($data);
        if (!$address) {
            throw new InvalidArgumentException('There Is No Addresss Available');
        }
        // $address->update([
        //     'user_id' => $user_id,
        //     'country' => $data['country'],
        //     'city' => $data['city'],
        //     'street' => $data['street'],
        //     'building' => $data['building'],
        //     'apartment' => $data['apartment'],
        //     'phone_number_one' => $data['phone_number_one'],
        //     'phone_number_two' => $data['phone_number_two'],
        // ]);

        return $address;
    }

    public function show(int $address_id): Address
    {
        $address = Address::findOrFail($address_id);

        return $address;
    }

    public function delete(int $address_id): void
    {
        $address = Address::findOrFail($address_id);

        $address->delete();
    }

    public function forceDelete(int $address_id): void
    {
        $address = Address::findOrFail($address_id);

        $address->forceDelete();
    }
}
