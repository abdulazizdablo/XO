<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Services\SectionService;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class SectionController extends Controller
{
    public function __construct(
        protected   SectionService $sectionService
        )
    {
   
    }

    public function index()//si
    {
        $sections = $this->sectionService->getAllSections();
        return response()->success(
                $sections,
            Response::HTTP_OK
        );
    }

    public function info()//si
    {
        $sections = $this->sectionService->getSectionsInfo();
        return response()->success(
                $sections,
            Response::HTTP_OK
        );
    }

    public function getSectionCategories()//si
    {
        try {
            $section_id = request('section_id');
            $categories = $this->sectionService->getSectionCategories($section_id);

            return response()->success(
                $categories,
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error($e->getMessage(),Response::HTTP_NOT_FOUND);
        }

    }


    public function getSectionCategoriesSubs()//si
    {
        try {
            $section_id = request('section_id');
            $categories = $this->sectionService->getSectionCategoriesSubs($section_id);

            return response()->success(
                $categories,
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error($e->getMessage(),Response::HTTP_NOT_FOUND);
        }   
    }
    
    public function getSectionCategoriesInfo()//si
    {
        try {
            $section_id = request('section_id');
            $categories = $this->sectionService->getSectionCategoriesInfo($section_id);

            return response()->success(
                $categories ,
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error($e->getMessage(),Response::HTTP_NOT_FOUND);
        }
    }

    public function getSectionSubCategories()//si
    {
        try {
            $section_id = request('section_id');
            $sub_categories = $this->sectionService->getSectionSubCategories($section_id);

            return response()->success(
                $sub_categories,
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error($e->getMessage(),Response::HTTP_NOT_FOUND);
        }
    }

}
