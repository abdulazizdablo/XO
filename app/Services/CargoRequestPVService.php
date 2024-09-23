<?php

namespace App\Services;

use App\Models\CargoRequest;
use App\Models\CargoRequestPV;

class CargoRequestPVService
{




    public function sendMany(CargoRequest $cargo_request, array $cargo_request_pv_data)
    {



        //  $cargo_requests_pv = CargoRequestPV::createMany($cargo_request_pv_data);

        $cargo_request_pv =  $cargo_request->cargo_requests_pv()->createMany($cargo_request_pv_data);



        //   $cargo_request_pv = CargoRequestPV::create()


        return $cargo_request_pv;
    }
}
