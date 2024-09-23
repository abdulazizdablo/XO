<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use App\Services\SubCategoryService;
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
        $category_id=request('category_id');
        $subCategories = $this->subCategoryService->getAllSubCategories($category_id);

        return response()->success(
           $subCategories
        , Response::HTTP_OK);
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

            return response()->success(
                $sub_category,
            Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->error( $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }


    public function getProductForSubCategory()
    {
        try {
            $sub_category_id = request('sub_category_id');
            $sub_category = $this->subCategoryService->getProductForSubCategory($sub_category_id);

            return response()->success(
               $sub_category,
            Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->error($e->getMessage(),
             Response::HTTP_NOT_FOUND);
        }
    }
	
	 public function getSubCategoriesCount()
    {
        $count = SubCategory::count();
        return response()->json(['count' => $count]);
    }
	
	
	
	public function getSubCategoriesBySlug(Request $request){
	
	
 $sub_category  = $this->subCategoryService->getSubCategoryBySlug($request->slug);
		
		return response()->success($sub_category,200);
	
	}

}
