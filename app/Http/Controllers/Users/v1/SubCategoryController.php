<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Services\SubCategoryService;
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
    public function index()//si
    {
        $category_id=request('category_id');
        $subCategories = $this->subCategoryService->getAllSubCategories($category_id);

        return response()->success(
           $subCategories
        , Response::HTTP_OK);
    }
}
