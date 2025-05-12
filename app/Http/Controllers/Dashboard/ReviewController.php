<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReviewController extends Controller
{

    public function __construct(
        protected  ReviewService $reviewService
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)//si
    {
        $filter_data = $request->only(['content', 'rating', 'created']);
        $reviews = $this->reviewService->getAllReviews($filter_data);

        return response()->success(
            $reviews,
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy()//si
    {
        try {
            $review_id = request('review_id');
            $this->reviewService->delete($review_id);

            return response()->success(
                'deleted success',
                Response::HTTP_OK
            );
        } catch (\Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

}
