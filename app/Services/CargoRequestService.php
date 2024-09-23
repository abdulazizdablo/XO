<?php

namespace App\Services;

use App\Models\CargoRequest;
use App\Enums\CargoRequestStatus;

class CargoRequestService
{



    public function sendRequest(array $cargo_request_data,int $inventory_id,int $employee_id)
    {



        // check if the user initiate the request is warhouse manager


        $cargo_request = CargoRequest::create([
            'to_inventory' => $inventory_id,
            'request_status_id' => CargoRequestStatus::OPEN,
            'request_id' => 'TW- ' . rand(10000,9999).rand(10000,9999),

         //   'recieved_packages' => $cargo_request_data['recieved_packages'],
         //   'ship_date' => $cargo_request_data['ship_date'],
            'status' => 'open',
            'employee_id' =>  $employee_id

        ]);


        return $cargo_request;
    }



    public function productVariationQuant()
    {
    }
}
