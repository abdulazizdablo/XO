<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Exception;
use App\Http\Requests\Color\StoreColorRequest;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ColorController extends Controller
{


    public function __construct(
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() //si
    {
        try {
            $search = request('search');
            $colors = Color::select('id', 'name', 'hex_code', 'sku_code');

            if ($search != null) {
                $colors = $colors->where('name->en', 'LIKE', '%' . $search . '%')
                    ->orWhere('name->ar', 'LIKE', '%' . $search . '%')
                    ->orWhere('hex_code', 'LIKE', '%' . $search . '%')
                    ->orWhere('sku_code', 'LIKE', '%' . $search . '%');
            }
            return response()->success(
                $colors->get(),
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreColorRequest $request) //si
    {
        try {

            $color_data = $request->validated();

            $color = Color::create([
                "name" => [
                    "en" => $color_data["name_en"],
                    "ar" =>  $color_data["name_ar"]
                ],
                "hex_code" => $color_data["hex_code"],
                "sku_code" => $color_data["sku_code"],
            ]);

            if (!$color) {
                throw new Exception('color not created successfully');
            }

            return response()->success(
                $color,
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }
}
