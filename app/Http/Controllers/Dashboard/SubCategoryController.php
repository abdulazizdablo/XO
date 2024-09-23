<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
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
        $subCategories = $this->subCategoryService->getAllSubCategories();

        return response()->success(
            $subCategories,
            Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        try {
            $category_id = request('category_id');
            $validate = Validator::make(
                $request->all(),
                [
                    'name_en' => 'required|string|max:255',
                    'name_ar' => 'required|string|max:255',
                    'image' => 'required|image|mimes:jpeg,bmp,png,webp,svg'
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors()
                    ,Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            if ($request->hasFile('image')) {
                $has_photo = true;
            }

            $validated_data = $validate->validated();
            $sub_category = $this->subCategoryService->createSubCategory($validated_data, $category_id);

            return response()->success(
                $sub_category
                , Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function assign()
    {
        try {
            $sub_id = request('sub_id');
            $product_id = request('product_id');
            $process = $this->subCategoryService->assignProductToSub($sub_id, $product_id);

            return response()->success(
                'product assigned successfully to sub-category',
                Response::HTTP_OK);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SubCategory  $sub_category
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $sub_category_id = request('sub_category_id');
            $sub_category = $this->subCategoryService->getSubCategory($sub_category_id);

            return response()->success([
                'sub_category' => $sub_category],
            Response::HTTP_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }

    public function getProductForSubCategory()
    {
        try {
            $sub_category_id = request('sub_category_id');
            $sub_category = $this->subCategoryService->getProductForSubCategory($sub_category_id);

            return response()->success([
                'sub_category' => $sub_category],
            Response::HTTP_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }

}
