<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{


    public function __construct(
        protected CategoryService $categoryService
    ) {
   
    }

    public function index()
    {
        $categories = $this->categoryService->getAllCategories();

        return response()->success([
            'Categories' => $categories
        ], Response::HTTP_OK);
    }
    
    public function counts()
    {
        $inventory_id = request('inventory_id');
        $product_id = request('product_id');

        $categories = $this->categoryService->getCategoryCounts($inventory_id , $product_id);

        return response()->success([
           $categories
        ], Response::HTTP_OK);
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
            $section_id = request('section_id');
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
                    ,Response::HTTP_OK
                );
            }

            $validated_data = $validate->validated();

            $category = $this->categoryService->createCategory($validated_data, $section_id, $section_id);

            return response()->success(
                $category
                , Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    
    
    

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $category_id = request('category_id');
            $category = $this->categoryService->getCategory($category_id);

            return response()->success(
                $category
                ,Response::HTTP_OK);
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
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $section_id = request('section_id');
            $category_id = request('category_id');

            $validate = Validator::make(
                $request->all(),
                [
                    'name_en' => 'required|string|max:255',
                    'name_ar' => 'required|string|max:255',
                    'image' => 'nullable|image|mimes:jpeg,bmp,png,webp,svg'
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_OK
                );
            }

            $validated_data = $validate->validated();

            $has_photo = false;
            if ($request->hasFile('image')) {
                $has_photo = true;
            }

            $category = $this->categoryService->updateCategory($validated_data, $category_id, $section_id, $has_photo);

            return response()->success(
                [
                    'data' => $category
                ],
                Response::HTTP_CREATED
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $category_id = request('category_id');
            $category = $this->categoryService->delete($category_id);

            return response()->success(
                [
                    'message' => 'Cateegory Deleted Successfully',
                    'data' => $category
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
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $category_id = request('category_id');
            $category = $this->categoryService->forceDelete($category_id);

            return response()->success(
                [
                    'message' => 'Cateegory Deleted Successfully',
                    'data' => $category
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

    public function getSubDataForCategory()
    {
        try {
            $category_id = request('category_id');
            $SubCategories = $this->categoryService->getSubDataForCategory($category_id);
            return response()->success(
                $SubCategories
            , Response::HTTP_OK);
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    public function getSubForCategory()
    {
        try {
            $category_id = request('category_id');
            $SubCategories = $this->categoryService->getSubForCategory($category_id);
            return response()->success(
                $SubCategories
            , Response::HTTP_OK);
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }
    
    
    public function getImageUrl(){
        
        $publicId = "photo/(7)";
        
      return  $this->categoryService->getcImageUrl($publicId);
        
    }
    
}
