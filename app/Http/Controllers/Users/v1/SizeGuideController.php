<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Services\SizeGuideService;
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
    public function index()//si
    {

        $sizes = $this->sizeGuideService->getAllSizeGuides();

        return response()->success(
            $sizes
        , Response::HTTP_OK);
    }

}
