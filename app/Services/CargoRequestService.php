<?php

namespace App\Services;

use App\Models\CargoRequest;
use App\Enums\CargoRequestStatus;

class CargoRequestService
{
    public function sendRequest(array $cargo_request_data,int $inventory_id,int $employee_id)//si
    {
        // check if the user initiate the request is warhouse manager
        $cargo_request = CargoRequest::create([
            'to_inventory' => $inventory_id,
            'request_status_id' => CargoRequestStatus::OPEN,
            'request_id' => 'TW- ' . random_int(1000,9999).random_int(1000,9999),
            'status' => 'open',
            'employee_id' =>  $employee_id
        ]);
        return $cargo_request;
    }

    public function productVariationQuant()
    {
    }
}
