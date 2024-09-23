<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
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
    public function index(Request $request)
    {
        $filter_data = $request->only(['content', 'rating', 'created']);
        $reviews = $this->reviewService->getAllReviews($filter_data);

        return response()->success(
            $reviews,
            Response::HTTP_OK
        );
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
            $user = auth('sanctum')->user();
            $user_id  = $user->id;
            $product_id = request('product_id');
            $rating = request('rating');
            $comment = request('comment');

            $validate = Validator::make(
                $request->all(),
                [
                    'rating' => 'required|numeric|between:1,5',
                    'comment' => 'required|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            // $validated_data = $validate->validated();
            // $review_data = $validated_data['review'];


            $review = $this->reviewService->createReview($rating, $comment, $user_id, $product_id);

            return response()->success(
                [
                    'message' => 'Review Is Created',
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
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $review_id = request('review_id');
            $review = $this->reviewService->getReview($review_id);

            return response()->success(
                [
                    'review' => $review
                ],
                Response::HTTP_FOUND
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            $user_id  = $user->id;
            $product_id = request('product_id');
            $review_id = request('review_id');
            $rating = request('rating');
            $comment = request('comment');


            $validate = Validator::make(
                $request->all(),
                [
                    'rating' => 'required|max:255',
                    'comment' => 'required|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_OK
                );
            }

            // $validated_data = $validate->validated();
            // $review_data = $validated_data['review'];

            $review = $this->reviewService->updateReview($rating, $comment, $review_id, $user_id, $product_id);

            return response()->success(
                [
                    'message' => 'Review Updated successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $review_id = request('review_id');
            $review = $this->reviewService->delete($review_id);

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $review_id = request('review_id');
            $reviewService = $this->reviewService->forceDelete($review_id);

            return response()->success(
                [
                    'message' => 'Review deleted successfully'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }
    public function reviewCount()
    {

        $reviews = $this->reviewService->reviewCount();

        return response()->success(
            $reviews,
            Response::HTTP_OK
        );
    }
}
