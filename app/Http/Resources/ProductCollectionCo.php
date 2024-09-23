<?php

namespace App\Http\Resources;

use App\Models\Color;
use App\Models\Photo;
use App\Models\Size;
use App\Traits\TranslateFields;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

class ProductCollectionCo extends ResourceCollection
{
    use TranslateFields;
    protected $notified_products;
    // protected $favourites_products;
    protected $auth_user;
    protected $auth_reviews;
    public function __construct($resource, $user = null, /*$favourites_products = null,*/ $notified_products = null, $user_reviews = null)
    {
        //parent::__construct($resource,$favourites_products);

        $this->auth_user = $user ?? null;
        $this->auth_reviews = $user_reviews ?? null;
        $this->notified_products = $notified_products ?? [];
        // $this->favourites_products = $favourites_products ?? [];



        $this->resource = $this->collectResource($resource); //->with( $this->favourites_products);

        //dd($this->resource);



    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = auth('sanctum')->user()->load('favourites_products', 'notifies', 'reviews');

        $color_fields = [
            'id',
            'name',
            'hex_code',
            'sku_code'
        ];

        // Check if the resource is an instance of AbstractPaginator
        if ($this->resource instanceof AbstractPaginator) {
            // Prepare pagination information
            $pagination = [
                "current_page" => $this->currentPage(),
                "first_page_url" => $this->getOptions()['path'] . '?' . http_build_query(array_merge($request->except('page'), ['page' => 1])),
                "prev_page_url" => $this->previousPageUrl(),
                "next_page_url" => $this->nextPageUrl(),
                "last_page_url" => $this->getOptions()['path'] . '?' . http_build_query(array_merge($request->except('page'), ['page' => $this->lastPage()])),
                "last_page" => $this->lastPage(),
                "per_page" => $this->perPage(),
                "total" => $this->total(),
                "path" => $this->getOptions()['path'],
            ];
        } else {
        }
     
        return [

            'products' => $this->collection->transform(function ($item) use ($user) {
                // Get pricing information for the product
                $pricing = $item->pricing;

                // Initialize variables for discount information
                $discount = null;
                $new_price = null;
                $discount_amount = null;
                $discount_percentage = null;

                // Check if the product has a discount
                $has_discount = $this->has('discount');

                $new_price = null;
                $discount_amount = null;
                $discount_percentage = null;

                $promotion_type = null;
                // Calculate the discount amount and new price
                if ($has_discount) {
                    $has_discount = true;
                    $discount = $item->discount;
                    $discount_amount = (
                        (($pricing->value ?? null) * $discount['percentage'])
                        / 100);
                    $new_price = ($pricing->value ?? null) - $discount_amount;
                    $discount_percentage = $discount['percentage'];
                    $promotion_type = 'discount';
                }



                // Check if the product is marked as notified by the user
                $is_notified = false;
                $is_favourite = false;

                if ($user->notifies == null) {

                    $is_notified = false;
                } else {
                    $is_notified = in_array($item->id, collect($user->notifies)->pluck('id')->toArray());
                }

                if ($user->favourites_products == null) {
                    $is_favourite = false;
                } else {

                    $is_favourite = in_array($item->id, collect($user->favourites_products)->pluck('id')->toArray());
                }

                // Get the product variations
                $product_variations = $item->product_variations;
                $product_variations_collection = new ProductVariationCollection(
                    $product_variations,
                   // collect($this->notified_products)->pluck('product_variations.*.id') ?? null,
                    // collect($this->favourites_products)->pluck('product_variations.*.id')
                );

                // Getting Color Ids for grouping
                $color_ids = $product_variations->flatten()->pluck('color_id')->unique();
                $size_ids = $product_variations->flatten()->pluck('size_id')->unique();
                // Getting Photos
                $photos = Photo::whereIn('color_id', $color_ids)
                    ->where('product_id', $item->id)
                    ->get();

                $sizes = Size::whereIn('id', $size_ids)->get();

                $reviews = ReviewsCollection::make($item->reviews, $item->id, $user, $user->reviews);

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'is_favourite' => $is_favourite,
                    'is_notified' => $is_notified,
                    'is_applied' => $item->is_applied,
                    
                    'has_discount' => $has_discount,
                    'promotion_type' => $promotion_type,
                    'discount' => $discount,
                    'group' => $item->group,
                    'product_variations' => $product_variations_collection
                        ->groupBy('color') // Grouping the variations by the 'color' attribute
                        ->map(function ($item2, $color) use ($item) {
                            $locale = app()->getLocale();
                            $color = Color::make(json_decode($color, true)); // Creating a Color object from the color data
                            return [
                                'color_id' => $color->id,
                                'color_hex_code' => $color->hex_code,
                                'color_name' => $color->name,
                                // 'color_name' => $color->getTranslation('name', $locale),

                                'items' => $item2->toArray(),
                            ];
                        })->values() // Resetting the keys of the collection to consecutive integers
                        ->toArray(), // Converting the collection to a plain PHP array,
                    'photos' => $photos->map(function ($item3) {
                        return [
                            "id" => $item3->id,
                            "product_id" => $item3->product_id,
                            "color_id" => $item3->color_id,
                            "path" => $item3->path,
                            "main_photo" => $item3->main_photo,
                        ];
                    }),


                    'sizes' => $sizes->map(function ($item2) {
                        return [
                            "id" => $item2->id,
                            "value" => $item2->value,
                            "type" => $item2->type,

                        ];
                    }),


                    'pricing' => [
                        'name' => $pricing->name ?? null,
                        'currency' => $pricing->currency ?? null,
                        'old_price' => $pricing->value ?? null,
                        'new_price' => $new_price,
                        'discount_amount' => $discount_amount,
                        'discount_percentage' => $discount_percentage,
                        'discount_start_date' => (is_null($discount)) ? null : $discount->start_date,
                        'discount_end_date' => (is_null($discount)) ? null : $discount->end_date,
                    ],
                    'reviews' => $reviews,
                ];
            }),
            // 'pagination' => $pagination,
        ];
    }
}
