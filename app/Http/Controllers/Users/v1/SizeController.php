<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Size;
use App\Services\SizeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
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

        $sizes = $this->sizeService->getAllSizes();

        return response()->success(
            $sizes,
            Response::HTTP_OK
        );
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
            $size = $this->sizeService->getSize($size_id);

            return response()->success(
                $size,
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

        $searched_size =  $this->sizeService->searchSize(request('search'));


        return response()->success($searched_size, Response::HTTP_CREATED);
    }
}
