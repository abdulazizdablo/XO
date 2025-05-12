<?php

namespace App\Http\Resources;

use App\Models\Color;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\TranslateFields;
use App\Http\Resources\ProductVariationCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Resources\ReviewsCollection;
use App\Traits\CloudinaryTrait;


use function PHPUnit\Framework\isNull;

class ProductResourceCopy extends JsonResource
{
    use TranslateFields, CloudinaryTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function __construct($product, protected User $user)
    {
        parent::__construct($product);
        $this->user = $user;
    }


    public function toArray($request): array
    {


        // $user_id = auth()->user()->id;getTranslatedFields
        //$user_id = 1;
        // $user = User::find($user_id);

        //   $favourites_products =  $this->user->favourites_products;
        // $notified_products =  $this->user->notified_products;

        $is_favourite =  $this->user->favourites_products->contains($this->id);
        $is_notified =  $this->user->notified_products->contains($this->id);

        //$is_favourite = null;
        // $is_notified = null;
        if ($this->user->favourites_products != null) {
            $is_favourite = $this->user->favourites_products->pluck('id')->contains($this->id);
        }

        if ($this->user->notified_products != null) {
            // $is_notified = null;
            $is_notified = $this->user->notified_products->pluck('id')->contains($this->id);



            // $is_notified = $this->notified_products->pluck('id')->contains($item->id);
        }
        $is_favourite =  $this->user->favourites_products->contains($this->id);
        $notified_products_variations  =  $this->user->notified_product_variations->pluck('id');



        //$items = $this->whenLoaded('product_variations');
      

     // $items->put('user', $this->user);

        $product_ids = $this->user->notified_products->pluck('id');
       // $items->put('product_ids', $notified_products_variations);
       // $product_variations =  ProductVariationResource::collection(  $this->whenLoaded('product_variations')/*$this->whenLoaded('product_variations'), $this->user,   $notified_products_variations /*,$this->user,$notified_products_variations*/);

       // $coupons = Coupons::paginate(15);
     //   $user = 'brand1';
      //  $brand_id = '123';
        


   
        $product_variations = $this->whenLoaded('product_variations')->map(function ($product_variation) use ($notified_products_variations) {

           
            return new ProductVariationResource($product_variation, $this->user, $notified_products_variations);
        });


//$product_variations =  ProductVariationCollection::collection($this->whenLoaded('product_variations'))->additional(['user' => $this->user,'notified_products' => $notified_products_variations ]);
       /* (new ProductVariationResource($productVariation))
            ->additional(['key' => 'value']);
*/


        $color_ids = [];
        if ($product_variations != null) {
            $color_ids = $product_variations->pluck('color_id');
        }

        $pricing = $this->pricing;

        $has_discount = $this->relationLoaded('discount');

        $discount = null;
        $new_price = null;
        $discount_amount = null;
        $discount_percentage = null;

        if ($has_discount) {
            $has_discount = true;
            $discount = $this->discount;
            if ($discount != null) {
                $discount_amount = floor(
                    (($pricing->value ?? null) * $discount['percentage'])
                    / 100);
                $new_price = ($pricing->value ?? null) - $discount_amount;
                $discount_percentage = $discount['percentage'];
            }
        }
        $reviews = $this->reviews;
        // $description = json_decode($this->description, true);
        // $description_ar = $description['ar'];

        //  $description_en = $description['en'];
      
        return [
            'id' => $this->id,
            'name' => $this->name,
            'available' => $this->available,
            'slug' => $this->slug,
            'description' => $this->description,
            // 'description_ar' => $description_ar,
            //'description_en' => $description_en,
            'is_favourite' => $is_favourite,
            'is_notified' => $is_notified,
            'product_sku_code' => $this->item_no,
            'season' => $this->season,
            'has_discount' => $has_discount,
            'discount' => $discount,
            'group' => $this->whenLoaded('group'),


            'product_variations' => $product_variations->groupBy('color')->map(function ($items, $color) {

     
             //   $color = new Color(json_decode($color,true));
      
            $color_json = (json_decode($color,true));
           // dd($color_json);
                $color = Color::make($color_json);
                //$color->exists = true;
            
                $locale = app()->getLocale();
                return [
                    'color_id' => $color->id,
                    'color_hex_code' => $color->hex_code,
                    'color_name' => $color->getTranslation('name', $locale),
                    'items' => $items->toArray(),
                ];

           
            })->values()->toArray(),
            'photos' => $this->photosByColorId($color_ids)->map(function ($item3) {
                return [
                    "id" => $item3->id,
                    "product_id" => $item3->product_id,
                    "color_id" => $item3->color_id,
                    "path" => $item3->path,
                    "main_photo" => $item3->main_photo,
                ];
            }),
            'reviews' => [
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
            ], 'pricing' => [
                'name' => $pricing->name ?? null,
                'currency' => $pricing->currency ?? null,
                'old_price' => $pricing->value ?? null,
                'new_price' => $new_price,
                'discuont_amount' => $discount_amount,
                'discount_percentage' => $discount_percentage,
                'discount_start_date' => (is_null($discount)) ? null : $discount->start_date,
                'discount_end_date' => (is_null($discount)) ? null : $discount->end_date,
            ],
        ];
    }

    public function additional(array $data)
    {
        if ($this->resource instanceof AbstractPaginator) {
            return [
                'pagination' => [
                    "current_page" => $this->currentPage(),
                    "first_page_url" =>  $this->getOptions()['path'] . '?' . $this->getOptions()['pageName'] . '=1',
                    "prev_page_url" =>  $this->previousPageUrl(),
                    "next_page_url" =>  $this->nextPageUrl(),
                    "last_page_url" =>  $this->getOptions()['path'] . '?' . $this->getOptions()['pageName'] . '=' . $this->lastPage(),
                    "last_page" =>  $this->lastPage(),
                    "per_page" =>  $this->perPage(),
                    "total" =>  $this->total(),
                    "path" =>  $this->getOptions()['path'],
                ]
            ];
        }
    }
}
