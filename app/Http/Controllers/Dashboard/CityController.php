<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Services\CityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class CityController extends Controller
{


    public function __construct(
        protected CityService $cityService
    ) {
     
    }

    public function index()//si
    {
        $cities = $this->cityService->getAllCities();

        return response()->success(
            $cities,
            Response::HTTP_OK
        );
    }

}
