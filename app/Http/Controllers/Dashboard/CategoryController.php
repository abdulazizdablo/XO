<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\CountCategoryRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Services\CategoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{

    public function __construct(
        protected CategoryService $categoryService
    ) {
   
    }

    public function counts(CountCategoryRequest $request)//si
    {
  
        $inventory_id = $validatedData['inventory_id'] ?? null;
   

        $categories = $this->categoryService->getCategoryCounts($inventory_id , $request->validated('product_id'));

        return response()->success([
           $categories
        ], Response::HTTP_OK);
    }


    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoryRequest $request)//si
    {
        try {

      
            $category = $this->categoryService->createCategory($request->validated(), $request->validated('section_id'));
            
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)//si
    {
        try {
            $section_id = request('section_id');
            $category_id = request('category_id');		
            $has_photo = false;

            if ($request->hasFile('image')) {
                $has_photo = true;
            }

            $category = $this->categoryService->updateCategory($request->all(), $category_id, $section_id, $has_photo);
            
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
    public function destroy(Request $request)//si
    {
        try {
            $validatedData = $request->validate([
				'category_id' => ['required', 'integer', 'exists:categories,id'],
			]);
			$category_id = $validatedData['category_id'];
			$category_product = Category::find($category_id)->products()->first();
			if(isset($category_product)){
				return response()->error(
                'You Can NOT Remove This Category Cause It Has Products',
                400
            );	
			}
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
                400
            );
        }
    }

    public function getSubDataForCategory(Request $request)//si
    {
        try {
            $validatedData = $request->validate([
                'category_id' => ['required', 'integer', 'exists:categories,id'],
            ]);
            $category_id = $validatedData['category_id'];            
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

    public function getSubForCategory(Request $request)//si
    {
        try {
            $validatedData = $request->validate([
                'category_id' => ['required', 'integer', 'exists:categories,id'],
            ]);
            $category_id = $validatedData['category_id'];   
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

    public function changeVisibility(Request $request){
        try{
			$validate = Validator::make(
				$request->all(),
                [
					'category_id' => 'required|exists:categories,id',					
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors()
                    ,Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
			
			$category_id = request('category_id');
			$category = Category::findOrFail($category_id);
			$is_valid = $category->valid;
			if($is_valid){
                $category->update(['valid'=>0]);
                $sub_category_ids = $category->subCategories->pluck('id');
                SubCategory::whereIn('id',$sub_category_ids)->update(['valid' => 0]);
                $products = Product::whereIn('sub_category_id', $sub_category_ids)->get();
                Product::whereIn('id', $products->pluck('id'))->update(['available' => 0]);	
			}
			else{
                $category->update(['valid'=>1]);
                $sub_category_ids = $category->subCategories->pluck('id');
                SubCategory::whereIn('id',$sub_category_ids)->update(['valid' => 1]);
                $products = Product::whereIn('sub_category_id', $sub_category_ids)->get();
                Product::whereIn('id', $products->pluck('id'))->update(['available' => 1]);	
			}
			return response()->success(
                'Category status was updated, this include all its subcategories and products'
            , Response::HTTP_OK);
        	
		}catch(InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }
    
}
