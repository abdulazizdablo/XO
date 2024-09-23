<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Comment;
use App\Services\ColorService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class ColorController extends Controller
{
 

    public function __construct(
        protected  ColorService $colorService
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
            $colors = Color::select('id', 'name', 'hex_code', 'sku_code');

            if($search != null){
                $colors = $colors->where('name->en','LIKE','%'.$search.'%')
                                ->orWhere('name->ar','LIKE','%'.$search.'%')
                                ->orWhere('hex_code','LIKE','%'.$search.'%')
                                ->orWhere('sku_code','LIKE','%'.$search.'%');
            }
            // $colors = $this->colorService->getAllColors($search);

            return response()->success(
                $colors->get(),
            Response::HTTP_OK);
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
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
                    'name_en' => 'required|max:255',
                    'name_ar' => 'required|max:255',
                    'hex_code' => 'required|unique:colors|max:255',
                    'sku_code' => 'required|unique:colors|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
            422
                );
            }

            $color_data = $validate->validated();

            $color = Color::create([
                "name" =>[
                    "en " => $color_data["name_en"],
                    "ar " =>  $color_data["name_ar"]
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $color = Color::findOrFail(request('color_id'));

            if (!$color) {
                throw new InvalidArgumentException('Color not found');
            }

            return response()->success(
                $color,
                Response::HTTP_FOUND
            );
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
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $color_id = request('color_id');
            $validate = Validator::make(
                $request->all(),
                [
                    'name_en' => 'string|max:25',
                    'name_ar' => 'string|max:25',
                    'hex_code' => 'string|max:25',
                    'sku_code' => 'string|max:25',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                  422
                );
            }

            $color_data = $validate->validated();

            $color = Color::findOrFail($color_id);

            if (!$color) {
                throw new InvalidArgumentException('There Is No Colors Available');
            }

            $color->update([
                "name" =>[
                    "en" => $color_data["name_en"],
                    "ar" =>  $color_data["name_ar"]
                ],
                "hex_code" => $color_data["hex_code"],
                "sku_code" => $color_data["sku_code"],
            ]);

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $color_id = request('color_id');
            $color = $this->colorService->delete($color_id);

            return response()->success(
                'Color deleted successfully',
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }
    public function search(){

        $searched_color =  $this->colorService->searchColor(request('search'));
 
 
         return response()->success($searched_color,Response::HTTP_CREATED);
 
 
         
     }
}
