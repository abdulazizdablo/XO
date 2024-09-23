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

    public function index()
    {
        $cities = $this->cityService->getAllCities();

        return response()->success(
            $cities,
            Response::HTTP_OK
        );
    }

    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'name_ar' => 'required|string|max:255',
                    'name_en' => 'required|string|max:255'
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $validated_data = $validate->validated();
            $city = $this->cityService->createCity($validated_data);

            return response()->success([
                'message' => 'City Is Created',
                'data' => $city
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
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

            return response()->success([
                'city' => $city
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\City  $city
     * @return \Illuminate\Http\Response
     */
    public function edit(City $city)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\City  $city
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $city_id = request('id');
            $validate = Validator::make(
                $request->all(),
                [
                    'id' => 'required|numeric',
                    'name_ar' => 'sometimes|string|max:255',
                    'name_en' => 'sometimes|string|max:255'
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $validated_data = $validate->validated();

            $city = $this->cityService->updateCity($validated_data);

            return response()->success(
                [
                    'message' => 'City Is Updated',
                    'data' => $city
                ],
                Response::HTTP_CREATED
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\City  $city
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $city_id = request('city_id');
            $city = $this->cityService->delete($city_id);

            return response()->success(
                [
                    'message' => 'City Deleted Successfully',
                    'data' => $city
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\City  $city
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $city_id = request('city_id');
            $city = $this->cityService->forceDelete($city_id);

            return response()->success(
                [
                    'message' => 'City Deleted Successfully',
                    'data' => $city
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
