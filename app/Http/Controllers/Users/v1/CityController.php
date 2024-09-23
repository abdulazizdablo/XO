<?php

namespace App\Http\Controllers\Users\v1;

use App\Models\City;
use App\Http\Controllers\Controller;
use App\Services\CityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class CityController extends Controller
{
 
    public function __construct(
        protected  CityService $cityService
    ) {
     
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cityes = $this->cityService->getAllCities();

        return response()->success(
            $cityes,
            Response::HTTP_OK
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\City  $city
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $city_id = request('city_id');
            $city = $this->cityService->getCity($city_id);

            return response()->success(
                $city,
                Response::HTTP_FOUND
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                  $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }


}
