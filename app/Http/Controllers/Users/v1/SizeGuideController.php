<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
// use App\Models\Size;
use App\Services\SizeGuideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class SizeGuideController extends Controller
{
   
    public function __construct(
        protected  SizeGuideService $sizeGuideService
        )
    {
    
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $sizes = $this->sizeGuideService->getAllSizeGuides();

        return response()->success(
            $sizes
        , Response::HTTP_OK);
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
            $sizeGuide_id = request('sizeGuide_id');
            $sizeGuide_id = $this->sizeGuideService->getSizeGuide($sizeGuide_id);

            return response()->success(
               $sizeGuide_id,
            Response::HTTP_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                  $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }


}
