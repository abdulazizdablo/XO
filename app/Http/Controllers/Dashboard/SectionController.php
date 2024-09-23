<?php

namespace App\Http\Controllers\Dashboard;

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
        protected SectionService $sectionService
    ) {
    }

    public function index()
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

    public function getSectionsSales(Request $request)
    {
        try {
            $section_id = request('section_id');
            $sections = $this->sectionService->getSectionSales($section_id);

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

    public function popularCategories(Request $request)
    {
        try {
            $categories_id = request('categories_id');
            $section_id = request('section_id');
            $dateScope = request('date_scope');
            $date = $request->only(['date']);
            // $sections = $this->sectionService->comparePopularCategories($categories_id, $section_id, $dateScope, $from_date, $to_date);
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
            // $categories = request('categories');
            $validate = Validator::make(
                $request->all(),
                [
                    'name_en' => 'required|string|max:255',
                    'name_ar' => 'required|string|max:255',
                    'type' => 'required|string|max:255',
                    'image' => 'image|mimes:jpeg,bmp,png,webp,svg'
                ],
                [
                    'name_en' => 'this name is required',
                    'name_ar' => 'this name is required',
                    'type.required' => 'this type is required',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();
            // $file = $request->file('image');
            $section = $this->sectionService->createSection($validated_data);
            return response()->success(
                [
                    'message' => 'Section Is Created',
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
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
            $section_id = request('Section_id');
            $section = $this->sectionService->getSection($section_id);

            return response()->json(['Section' => $section]);
        } catch (InvalidArgumentException $e) {
            return response()->json($e->getMessage(), 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function edit(Section $section)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $section_id = request('Section_id');

            $validate = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                ],
                [
                    'name.required' => 'this name is required',
                ]
            );

            if ($validate->fails()) {
                return response()->json(
                    $validate->errors()

                );
            }

            $validated_data = $validate->validated();
            $section_data = $validated_data['section'];

            $section = $this->sectionService->updateSection($section_data, $section_id);

            return response();
        } catch (\Throwable $th) {
            return response()->json(
                $th->getMessage()
            );
            // DB::rollback();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $section_id = request('Section_id');
            $sectionService = $this->sectionService->delete($section_id);

            return response()->json(['message' => 'Section deleted successfully']);
        } catch (InvalidArgumentException $e) {
            return response()->json($e->getMessage(), 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {

        try {
            $section_id = request('id');
            $sectionService = $this->sectionService->forceDelete($section_id);

            return response()->json(['message' => 'Section deleted successfully']);
        } catch (InvalidArgumentException $e) {
            return response()->json($e->getMessage(), 404);
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
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
        //     return response()->json(['Section' => $section]);
        // } catch (InvalidArgumentException $e) {
        //     return response()->json($e->getMessage(), 404);
        // }
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
        //     return response()->json(['Section' => $section]);
        // } catch (InvalidArgumentException $e) {
        //     return response()->json($e->getMessage(), 404);
        // }
    }
    public function getSectionsCategories()
    {
        try {
            $section_id = request('section_id');
            $section = $this->sectionService->getSectionCategories($section_id);

            return response()->json(['Section' => $section]);
        } catch (InvalidArgumentException $e) {
            return response()->json($e->getMessage(), 404);
        }
    }
}
