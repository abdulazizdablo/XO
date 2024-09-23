<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Size;
use App\Services\SizeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class SizeController extends Controller
{


    public function __construct(
        protected  SizeService $sizeService
    ) {
       
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Size  $Size
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $size_id = request('size_id');
            $size = Size::findOrFail($size_id);

          

            return response()->success(
                $size,
                Response::HTTP_FOUND
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function store(Request $request)
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


    public function update(Request $request)
    {
        try {
            $size_id = request('size_id');
            $validate = Validator::make(
                $request->all(),
                [
                    'name' => 'unique:sizes|max:255',
                    'hex_code' => 'unique:sizes|max:255',
                    'sku_code' => 'unique:sizes|max:255',
                    'type'     => 'string'
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_OK
                );
            }

            $size_data = $validate->validated();

            $size = Size::findOrFail($size_id);

        

            $size->update($size_data);

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $size_id = request('size_id');
            $size = Size::findOrFail($size_id);

           /* if (!$size) {
                throw new Exception('Size not found');
            }*/

            $size->delete();

            return response()->success(
                'Size deleted successfully',
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }


    public function search(){

       $searched_size =  $this->sizeService->searchSize(request('search'));

        return response()->success($searched_size,Response::HTTP_CREATED);


        
    }
}
