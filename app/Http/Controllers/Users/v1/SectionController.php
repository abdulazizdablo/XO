<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Services\SectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class SectionController extends Controller
{

   

    public function __construct(
        protected   SectionService $sectionService
        )
    {
   
    }

    public function index()
    {
        $sections = $this->sectionService->getAllSections();
        return response()->success(
                $sections,
            Response::HTTP_OK
        );
    }

    public function info()
    {
        $sections = $this->sectionService->getSectionsInfo();
        return response()->success(
                $sections,
            Response::HTTP_OK
        );
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $section_id = request('section_id');
            $section = $this->sectionService->getSection($section_id);

            return response()->success(

                    $section,

                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error($e->getMessage(),Response::HTTP_NOT_FOUND);

        }
    }

    public function getSectionCategories()
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


    public function getSectionCategoriesSubs()
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
    
    public function getSectionCategoriesInfo()
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

    public function getSectionSubCategories()
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
