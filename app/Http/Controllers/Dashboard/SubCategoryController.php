<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use App\Models\Product;
use App\Services\SubCategoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class SubCategoryController extends Controller
{

    public function __construct(
        protected  SubCategoryService $subCategoryService
    ) {}

    public function store(Request $request) //si
    {
        try {
            $category_id = request('category_id');
            $validate = Validator::make(
                $request->all(),
                [
                    'name_en' => 'required|string|max:255',
                    'name_ar' => 'required|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            $validated_data = $validate->validated();
            $sub_category = $this->subCategoryService->createSubCategory($validated_data, $category_id);

            return response()->success(
                $sub_category,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function update(Request $request) //si
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'name_en' => 'nullable|string|max:255',
                    'name_ar' => 'nullable|string|max:255',
                    'category_id' => 'nullable|exists:categories,id',
                    'sub_category_id' => 'required|exists:sub_categories,id',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $sub_category_id = request('sub_category_id');
            $validated_data = $validate->validated();
            $sub_category = $this->subCategoryService->updateSubCategory($validated_data, $sub_category_id);

            return response()->success(
                $sub_category,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function assign() //si
    {
        try {
            $sub_id = request('sub_id');
            $product_id = request('product_id');
            $this->subCategoryService->assignProductToSub($sub_id, $product_id);
            return response()->success(
                'product assigned successfully to sub-category',
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function destroy(Request $request)//si
    { 
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'sub_category_id' => 'required|exists:sub_categories,id',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $sub_category_id = request('sub_category_id');
			$subCategory_product = SubCategory::find($sub_category_id)->products()->first();
			if(isset($subCategory_product)){
				return response()->error(
                'You Can NOT Remove This Sub Category Cause It Has Products',
                400
            );	
			}
            $subCategory = SubCategory::findOrFail($sub_category_id);
            $subCategory->delete();
            $products = Product::where('sub_category_id', $sub_category_id)->get();
            Product::whereIn('id', $products->pluck('id'))->update(['available' => 0]);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    public function changeVisibility(Request $request)//si
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'sub_category_id' => 'required|exists:sub_categories,id',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $sub_category_id = request('sub_category_id');
            $subCategory = SubCategory::findOrFail($sub_category_id);
            $is_valid = $subCategory->valid;
            if ($is_valid) {
                $subCategory->update(['valid' => 0]);

                $products = Product::where('sub_category_id', $sub_category_id)->get();
                Product::whereIn('id', $products->pluck('id'))->update(['available' => 0]);
            } else {
                $subCategory->update(['valid' => 1]);

                $products = Product::where('sub_category_id', $sub_category_id)->get();
                Product::whereIn('id', $products->pluck('id'))->update(['available' => 1]);
            }
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
