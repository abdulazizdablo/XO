<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
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

    public function index()
    {
        $per_page = 4;
        $page = request('page');
        $categories = $this->categoryService->getAllAvailableCategories($per_page, $page);
        // $pagination = $categories->toArray();

        return response()->success(
            $categories,
            Response::HTTP_OK
        );
    }

    public function getSubForCategory()
    {
        $category_id = request('category_id');
        $SubCategories = $this->categoryService->getSubForCategory($category_id);
        return response()->success(
            $SubCategories,
            Response::HTTP_OK
        );
    }
	public function getCategoriesBySlug(Request $request){
	
	
 $category  = $this->categoryService->getCategoriesBySlug($request->slug);
		
		return response()->success($category,200);
	
	}
	
}
