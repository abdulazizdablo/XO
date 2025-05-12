<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Review;
use Exception;
use InvalidArgumentException;
use Illuminate\Support\Str;

class ReviewService
{
	public function getAllReviews($filter_data) //si
	{
		$reviews = Review::query()->with([
			'user:id,first_name,last_name,banned,deleted_at',
			'product:id,name',
			'product.main_photos:id,product_id,thumbnail',
		])->whereHas('user', function ($query) {
			$query->whereNull('deleted_at');
		});

		if (!empty($filter_data)) {
			$reviews = $this->applyFilters($reviews, $filter_data);
		}

		if (!$reviews) {
			throw new InvalidArgumentException('There Is No Reviews Available');
		}

		return $reviews->paginate(8);
	}

	protected function applyFilters($query, array $filters)
	{
		$appliedFilters = [];
		foreach ($filters as $attribute => $value) {
			$column_name = Str::before($attribute, '_');
			$method = 'filterBy' . Str::studly($column_name);
			if (method_exists($this, $method) && isset($value) && !in_array($column_name, $appliedFilters)) {
				$query = $this->{$method}($query, $filters);
				$appliedFilters[] = $column_name;
			}
		}

		return $query;
	}


	protected function filterByRating($query, $filter_data)
	{
		return $query->where('rating', $filter_data['rating']);
	}

	protected function filterByContent($query, $filter_data)
	{
		return $query->where('comment', 'LIKE', '%' . $filter_data['content'] . '%')->orWhereHas('product', function ($q) use ($filter_data) {
			$q->where('name->en', 'like', '%' . $filter_data['content'] . '%')
				->orWhere('name->ar', 'like', '%' . $filter_data['content'] . '%');
		});
	}

	protected function filterByCreated($query, $filter_data)
	{
		return $query->whereDate('created_at', $filter_data['created']);
	}

	public function createReview($rating, $comment, $user, int $product_id, $page_size) //si
	{
		$review = Review::create([
			'user_id' => $user->id,
			'product_id' => $product_id,
			'rating' => $rating,
			'comment' => $comment,
		]);

		if (!$review) {
			throw new InvalidArgumentException('Something Wrong Happend');
		}

		$product = Product::where('id', $product_id)->withCount('reviews')->withAvg('reviews', 'rating')->firstOrFail();

		$avg_rating = $product->reviews_avg_rating;

		$ratings = Review::where('product_id', $product_id)->get()->groupBy('rating');




		$percentages = $ratings->map(function ($item, $key) use ($product) {
			return [
				'rating' => $key,
				'count' => $item->count('rating'),
				'percentage' => (string) round($item->count('rating') * 100 / $product->reviews_count, 1)
			];
		})->values()->values();

		$reviews_count = $product->reviews_count;
		if ($user) {
			$user_review = $product->reviews()->with('user:id,first_name,last_name')->where('user_id', optional($user)->id)->first();
		} else {
			$user_review = null;
		}

		$product_reviews = $product->reviews()->with('user:id,first_name,last_name')->paginate($page_size);

		$product_reviews = $product_reviews->filter(function ($model) use ($user_review) {
			return $model->id == $user_review?->id;
		});

		return [
			'reviews_count' => $reviews_count,
			'avg_rating' => $avg_rating,
			'rating' => $percentages,
			'user_review' =>  $user_review,
			'reviews' =>  $product_reviews->values()
		];

		return $review;
	}

	public function updateReview($validated_data, $review_id, $user, $page_size) //si
	{
		$review = Review::find($review_id);
		$review->update($validated_data);

		if (!$review) {
			throw new InvalidArgumentException('There Is No Reviews Available');
		}

		$product = Product::where('id', $review->product->id)->withCount('reviews')->withAvg('reviews', 'rating')->first();

		if (!$product) {
			throw new Exception('Product does not exist');
		}

		$avg_rating = $product->reviews_avg_rating;
		$ratings = Review::where('product_id', $product->id)
			->groupBy('rating')
			->selectRaw('rating, count(*) as count, (count(*) * 100 / (select count(*) from reviews where product_id = ?)) as percentage', [$product->id])
			->get();
		$reviews_count = $product->reviews_count;

		if ($user) {
			$user_review = $product->reviews()->with('user:id,first_name,last_name')->where('user_id', optional($user)->id)->first();
		} else {
			$user_review = null;
		}
		return [
			'reviews_count' => $reviews_count,
			'avg_rating' => $avg_rating,
			'rating' => $ratings,
			'user_review' =>  $user_review,
			'reviews' => $product->reviews()->with('user:id,first_name,last_name')->paginate($page_size)
		];

		return $review;
	}

	public function showProductReviews($product_slug, $page_size) //si
	{
		$user = auth('sanctum')->check() ? auth('sanctum')->user() : null;
		$product = Product::where('slug', $product_slug)->with('reviews.user', function ($query) {
			$query->select(['id', 'first_name', 'last_name']);
		})->withCount('reviews')->withAvg('reviews', 'rating')->first();
		
		if (!$product) {
			throw new Exception('Product does not exist');
		}
		
		$avg_rating = $product->reviews_avg_rating;
		$ratings = Review::where('product_id', $product->id)->get()->groupBy('rating');
		$percentages = collect([]);
		$secondCollectionArray = $ratings->toArray();
		$percentages = $ratings->map(function ($item, $key) use ($product) {
			return [
				'rating' => $key,
				'count' => $item->count('rating'),
				'percentage' => (string) round($item->count('rating') * 100 / $product->reviews_count, 1)
			];
		})->values()->values();

		for ($i = 1; $i < 6; $i++) {
			$secondCollectionArray[$i] = [
				'rating' => $i,
				'count' => 0,
				'percentage' => (string) 0
			];
		}

		foreach ($percentages as $percentageItem) {
			$ratingKey = $percentageItem['rating'];

			if (isset($secondCollectionArray[$ratingKey])) {
				$secondCollectionArray[$ratingKey]['count'] = $percentageItem['count'];
				$secondCollectionArray[$ratingKey]['percentage'] = $percentageItem['percentage'];
			}
		}
		$updatedPercentages = collect(array_values($secondCollectionArray))->sortBy('rating')->values();

		$percentages = $ratings->map(function ($item, $key) use ($product) {
			return [
				'rating' => $key,
				'count' => $item->count('rating'),
				'percentage' =>  (string) ($item->count('rating') * 100 / $product->reviews_count)
			];
		})->values()->values();

		$reviews_count = $product->reviews_count;
		
		if ($user) {
			$user_review = $product->reviews()->with('user:id,first_name,last_name')->where('user_id', optional($user)->id)->first();
		} else {
			$user_review = null;
		}

		$product_reviews = $product->reviews()->with('user:id,first_name,last_name')->paginate($page_size);

		if ($user_review) {
			$product_reviews = $product->reviews()->where('id', '!=', $user_review->id)->with('user:id,first_name,last_name')->paginate($page_size);
		} else {
			$user_review = null;
		}

		return [
			'reviews_count' => $reviews_count,
			'avg_rating' => $avg_rating,
			'rating' => $updatedPercentages,
			'user_review' =>  $user_review,
			'reviews' => $product_reviews,
			'reviews' => [
				'data' => $product_reviews->items(),
				'current_page' => $product_reviews->currentPage(),
				'first_page_url' => $product_reviews->url(1),
				'from' => $product_reviews->firstItem(),
				'last_page' => $product_reviews->lastPage(),
				'last_page_url' => $product_reviews->url($product_reviews->lastPage()),
				'links' => $product_reviews->links(),
				'next_page_url' => $product_reviews->nextPageUrl(),
				'path' => $product_reviews->path(),
				'per_page' => $product_reviews->perPage(),
				'prev_page_url' => $product_reviews->previousPageUrl(),
				'to' => $product_reviews->lastItem(),
				'total' => $product_reviews->total()
			],
		];
	}

	public function delete(int $review_id): void //si
	{
		try {
			$review = Review::findOrFail($review_id);

			$review->delete();
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}
}
