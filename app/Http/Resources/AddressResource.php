<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

  
        return [
            'first_name' =>  User::findOrFail($this->first()->user_id)->first_name,
            'last_name' => User::findOrFail($this->first()->user_id)->last_name,
            'city' => $this->city,
            'neighborhood' => $this->neighborhood,
            'street' => $this->street,
            'another_details' => $this->another_details,
            'lat_long' => $this->lat_long,
            'phone_number_two' => $this->phone_number_two
        ];
    }
}
