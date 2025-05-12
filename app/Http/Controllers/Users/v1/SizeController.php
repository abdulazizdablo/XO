<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Services\SizeService;
use Symfony\Component\HttpFoundation\Response;

class SizeController extends Controller
{


    public function __construct(
        protected  SizeService $sizeService
    ) {
    }

    public function index()//si
    {
        $sizes = $this->sizeService->getAllSizes();
        return response()->success(
            $sizes,
            Response::HTTP_OK
        );
    }
}
