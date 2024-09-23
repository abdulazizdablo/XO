<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class PackageController extends Controller
{


    public function __construct(
        protected  PackageService $packageService
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
        try {
            $products = $this->packageService->getAllPackages();

            return response()->success(
                $products
            , Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_NOT_FOUND
            );
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $package_id = request('package_id');
            $package = $this->packageService->getPackage($package_id);

            return response()->success(
              $package,
            Response::HTTP_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                 $e->getMessage(),
                  Response::HTTP_NOT_FOUND);
        }
    }

}
