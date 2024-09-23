<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth('sanctum')->user();
        $user_id  = $user->id;
        $reviews = $this->reviewService->getAllReviewsByUserId($user_id);

        return response()->success(
          $reviews
        , Response::HTTP_OK);
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
            $user=auth('sanctum')->user();
            if(!$user){
                throw new Exception('Please sign in first');
            }
            $user_id  =$user->id;
            // $user_id = 1;
            $page_size = request('pageSize');
            $product_id = request('product_id');
            $rating = request('rating');
            $comment = request('comment');
            $validate = Validator::make($request->all(),
                [
                    'rating' => 'required|numeric|in:1,2,3,4,5',
                    'comment' => 'required|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                          $validate->errors()

                    , Response::HTTP_BAD_REQUEST
                );
            }

            // $validated_data = $validate->validated();
            // $review_data = $validated_data['review'];

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

    public function show()
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

	
	public function userReviews(Request $request){
	
		
		{
        $user = auth('sanctum')->user();
    if($user){
            $user_review = $user->reviews()->get();
	
        }
        else{
            $user_review = null;    
        }

        return response()->success(
          $user_review
        , Response::HTTP_OK);
    }
		
		
		
	}

    public function update(Request $request)
    {
        try {
            $user=auth('sanctum')->user();
            $user_id  =$user->id;
            // $product_id = request('product_id');
            $page_size = request('pageSize');
            $review_id = request('review_id');
            $rating = request('rating');
            $comment = request('comment');

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
            // $review_data = $validated_data['review'];

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
    public function destroy()
    {
        try {
            $review_id = request('review_id');
            $reviewService = $this->reviewService->delete($review_id);

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
