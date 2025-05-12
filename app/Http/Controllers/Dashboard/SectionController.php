<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\SectionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SectionController extends Controller
{

    public function __construct(
        protected SectionService $sectionService
    ) {
    }

    public function index()//si
    {
        try {
            $sections = $this->sectionService->getAllSections();

            return response()->success(
                $sections,
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    public function popularCategories(Request $request)//si
    {
        try {
            $categories_id = request('categories_id');
            $section_id = request('section_id');
            $dateScope = request('date_scope');
            $date = $request->only(['date']);
            $sections = $this->sectionService->comparePopularCategories($categories_id, $section_id, $date, $dateScope);

            return response()->success(
                $sections,
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }
    
    public function subCategoriesProducts()
    {
        try {
            $sub_category_id = request('sub_category_id');
            $visible = request('visible');
            $date = request('date');
            $date_min = request('date_min');
            $date_max = request('date_max');
            $key = request('search');
            $products = $this->sectionService->getSubCategoriesProducts($sub_category_id, $date, $date_min, $date_max, $visible, $key);

            return response()->success(
                $products,
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    public function getSectionCategories()//si
    {
        try {
            $section_id = request('section_id');
            $categories = $this->sectionService->getDashSectionCategories($section_id);

            return response()->success(
                $categories,
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
