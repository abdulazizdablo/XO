<?php

namespace App\Services;

use App\Models\CargoRequest;

class CargoRequestPVService
{
    public function sendMany(CargoRequest $cargo_request, array $cargo_request_pv_data)//si
    {
        $cargo_request_pv =  $cargo_request->cargo_requests_pv()->createMany($cargo_request_pv_data);
        return $cargo_request_pv;
    }
}
