<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class SizeController extends Controller
{


    public function __construct() {  
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $search = request('search');
            $type = request('type');
            $sizes = Size::select('id', 'value', 'sku_code', 'type');
            if ($type != null) {
                $sizes = $sizes->where('type',$type);
                    
            }

            if ($search != null) {
                $sizes = $sizes->where('value->en', 'LIKE', '%' . $search . '%')
                    ->orWhere('value->ar', 'LIKE', '%' . $search . '%')
                    ->orWhere('sku_code', 'LIKE', '%' . $search . '%')
                    ->orWhere('type', 'LIKE', '%' . $search . '%');
            }
           

            if (!$sizes) {
                throw new Exception('There Is No Sizes Available');
            }

            return response()->success(
                $sizes->get(),
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function store(Request $request)//si
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'value_en' => 'required|max:255',
                    'value_ar' => 'required|max:255',
                    'sku_code' => 'required|unique:sizes|max:255',
                    'type' =>     'required|string'
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $size_data = $validate->validated();

            $size = Size::create([
                "value" => [
                    "en" => $size_data["value_en"],
                    "ar" =>  $size_data["value_ar"]
                ],
                "sku_code" => $size_data['sku_code'],
                "type" => $size_data['type']
            ]);

            if (!$size) {
                throw new Exception('Something Wrong Happend');
            }

            return response()->success(
                $size,
                Response::HTTP_CREATED
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }
}
