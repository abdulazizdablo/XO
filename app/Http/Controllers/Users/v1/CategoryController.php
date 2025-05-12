<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;

use App\Models\Category;
use App\Services\CategoryService;

use App\Http\Requests\Category\GetSubCategoryRequest;
use App\Http\Requests\Category\GetSubCategoryBySlugRequest;

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

    public function getSubForCategory(GetSubCategoryRequest $request)//si
    {
        
        $SubCategories = $this->categoryService->getSubForCategory($request->validated('category_id'));
        
        return response()->success(
            $SubCategories,
            Response::HTTP_OK
        );
    }
    
	public function getCategoriesBySlug(GetSubCategoryBySlugRequest $request){//si
	

        $category  = $this->categoryService->getCategoriesBySlug($request->validated('slug'));
		
		return response()->success(
            $category,
            Response::HTTP_OK
        );
	
	}
	
}
