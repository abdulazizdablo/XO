<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class ReviewController extends Controller
{

    public function __construct(
        protected  ReviewService $reviewService
        )
    {

    }

    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)//si
    {
        try {
            $user=auth('sanctum')->user();
            
            if(!$user){
                throw new Exception('Please sign in first');
            }

            $page_size = request('pageSize');
            $product_id = request('product_id');
            $rating = request('rating');
            $comment = request('comment');
			
			$messages = [
           'rating.in' => 'Please pick a rate',
];
            $validate = Validator::make($request->all(),
                [
                    'rating' => 'required|numeric|in:1,2,3,4,5',
                    'comment' => 'required|string|max:255',
                ],
			   $messages
            );

            if ($validate->fails()) {
                return response()->error(
                          $validate->errors()

                    , Response::HTTP_BAD_REQUEST
                );
            }

            $review = $this->reviewService->createReview($rating ,$comment, $user, $product_id, $page_size);

            return response()->success(
                $review,
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_BAD_REQUEST
            );
        }

    }

    public function show()//si
    {
        try {
            $product_slug = request('product_slug');
            $page_size = request('pageSize');
			if(!$page_size){
				$page_size = 5;	
			}
            $reviews = $this->reviewService->showProductReviews($product_slug, $page_size);

            return response()->success(
                $reviews
                ,Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_NOT_FOUND
            );
        }		
	}

    public function update(Request $request)//si
    {
        try {
            $user=auth('sanctum')->user();
            $page_size = request('pageSize');
            $review_id = request('review_id');

            $validate = Validator::make($request->all(),
                [
                    'rating' => 'sometimes|numeric|between:1,5',
                    'comment' => 'sometimes|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                          $validate->errors()
                    , 422
                );
            }

            $validated_data = $validate->validated();

            $review = $this->reviewService->updateReview($validated_data, $review_id, $user, $page_size);

            return response()->success(
                $review,
                Response::HTTP_CREATED
            );

        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_OK
            );
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy() //si
    {
        try {
            $review_id = request('review_id');
            $this->reviewService->delete($review_id);

            return response()->success(
                [
                    'message' => 'Review deleted successfully'
                ]
                ,Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_NOT_FOUND
            );
        }
    }

}
