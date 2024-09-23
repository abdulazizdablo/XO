<?php

namespace App\Http\Controllers\Dashboard;

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
        protected PackageService $packageService
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
            $products = $this->packageService->getAllPackages();

            return response()->success([
                'products' => $products
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
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
                    'name_ar' => 'required|max:255|string',
                    'type' => 'required|string',
                    'image' => 'nullable|array|max:3',
                    'valid' => 'required|boolean',
                    'image' => 'required|image|mimes:jpeg,bmp,png,webp,svg'
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();

            $package = $this->packageService->createPackage($validated_data);

            return response()->success(
                [
                    'message' => 'Package Is Created',
                ],
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
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $package_id = request('package_id');
            $package = $this->packageService->getPackage($package_id);

            return response()->success(
                [
                    'package' => $package
                ],
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
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function edit(Package $package)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $package_id = request('package_id');

            $validate = Validator::make(
                $request->all(),
                [
                    'name' => 'sometimes|string|max:255',
                    'type' => 'sometimes|string|max:255',
                    'image' => 'sometimes|image|max:512|mimes:jpg,jpeg,bmp,png,webp,svg,heic',
                    'valid' => 'sometimes|boolean',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();

            $package = $this->packageService->updatePackage($validated_data, $package_id);

            return response()->success(
                [
                    'message' => 'Package Updated successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
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
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $package_id = request('package_id');
            $packageService = $this->packageService->delete($package_id);

            return response()->success(
                [
                    'message' => 'Product deleted successfully'
                ],
                Response::HTTP_OK
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
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $package_id = request('package_id');
            $packageService = $this->packageService->forceDelete($package_id);

            return response()->success(
                [
                    'message' => 'Package deleted successfully'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }
}
