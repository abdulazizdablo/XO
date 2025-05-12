<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Services\ColorService;
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

        $colors = $this->colorService->getAllColors();

        return response()->success(
            $colors,
            Response::HTTP_OK
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Color  $Color
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
			
            $color = $this->colorService->getColor(request('color_id'));

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
    public function search()
    {

		
		try {
        $searched_color =  $this->colorService->searchColor(request('search'));

         return response()->success($searched_color,Response::HTTP_CREATED);
 
		}
		 catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
		
         
     }

}
