<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

class ReviewsCollection extends ResourceCollection
{
    protected $product_id;
    protected $user_reviews;
    protected $auth_user;

    public function __construct($reviews, $product_id, $user, $auth_review)
    {
        parent::__construct($reviews);
        $this->auth_user = $user ? $user : null;
        $this->auth_review = $auth_review ? $auth_review : null;
        $this->product_id = $product_id;
        $this->user_reviews = $user->reviews;

       
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->resource instanceof AbstractPaginator) {
            $pagination = [
                "current_page" => $this->currentPage(),
                "first_page_url" =>  $this->getOptions()['path'] . '?' . $this->getOptions()['pageName'] . '=1',
                "prev_page_url" =>  $this->previousPageUrl(),
                "next_page_url" =>  $this->nextPageUrl(),
                "last_page_url" =>  $this->getOptions()['path'] . '?' . $this->getOptions()['pageName'] . '=' . $this->lastPage(),
                "last_page" =>  $this->lastPage(),
                "per_page" =>  $this->perPage(),
                "total" =>  $this->total(),
                "path" =>  $this->getOptions()['path'],
            ];
        } else {
            $pagination = null;
        }

        $reviews = $this->collection;
        // $auth_user = auth('sanctum')->user();
        if ($this->auth_user) {
            // $auth_review = $this->auth_user->reviews
            //     ->where('product_id', $this->product_id)
            //     ->first();

            if ($this->auth_review) {
// Assuming $this->auth_review is a collection of review objects.
    $authReviewIds = $this->auth_review->pluck('id')->toArray();

// Now, use $authReviewIds with whereNotIn to filter $this->collection.
   $reviews_data = $this->collection->whereNotIn('id', $authReviewIds)->toArray();
            } else {
                $reviews_data = $this->collection;
            }
        } else {
            $auth_review = null;
            $reviews_data = $this->collection;
        }
        // return parent::toArray($request);
        return [
            'reviews_count' => $reviews->count(),
            'reviews_avg' => round($reviews->avg('rating')),
            'percentages' => $reviews->groupBy('rating')->map(function ($item) use ($reviews) {
                $count = $item->count();
                $total = $reviews->count();
                $percentage = ($count / $total) * 100;
                $ratings = [0, 1, 2, 3, 4, 5];
                return [
                    'count' => $count,
                    'percentage' => round($percentage, 0),
                ];
            })->union(
                collect([1, 2, 3, 4, 5])->diff(collect($reviews)->pluck('rating'))->mapWithKeys(function ($rating) {
                    return [$rating => null];
                })
            )->sortByDesc(function ($value, $key) {
                return $key;
            }),

            'my_review' => $this->auth_review,
            'reviews_data' => $reviews_data,
            'pagination' => $pagination,
        ];
    }
}
