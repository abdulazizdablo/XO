<?php

namespace App\Http\Resources;

use App\Models\Color;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\TranslateFields;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Resources\ReviewsCollection;

use function PHPUnit\Framework\isNull;

class ProductTranslatedResource extends JsonResource
{
    use TranslateFields;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array
    {
        // $user_id = auth()->user()->id;getTranslatedFields
        $user_id = 1;
        $user = User::find($user_id);

        // $favourites_products = $user->favourites_products;
        // $notified_products = $user->notified_products;

        // $is_favourite = $user->favourites_products->contains($this->id);

        $is_favourite = null;
        $is_notified = null;
        /*if ($this->favourites_products != null) {
            $is_favourite = $user?->favourites_products->pluck('id')->contains($this->id);
        }*/

        if ($this->notified_products != null) {
            // $is_notified = null;
            $is_notified  = $user?->notified_products->pluck('id')->contains($this->id);
            // $is_notified = $this->notified_products->pluck('id')->contains($item->id);
        }
        // $is_favourite = $this->favourites_products->contains($this->id);
        // $is_notified  = $this->notified_products_ids->pluck('id')->contains($this->id);

        $product_variations = ProductVariationResource::collection($this->whenLoaded('product_variations'));
        $color_ids = collect($product_variations)->pluck('color_id');
        $pricing = $this->pricing;

        $has_discount = $this->discount_id ?? false;

        $discount = null;
        $new_price = null;
        $discount_amount = null;
        $discount_percentage = null;

        if ($has_discount) {
     
            $has_discount = true;
            $discount = $this->discount ?? null;



            $discount_amount = (
                (($pricing->value ?? null) * ($discount['percentage'] ?? null))
                / 100);
            $new_price = ($pricing->value ?? null) - $discount_amount;
            $discount_percentage = $discount['percentage'];
        }
       
        $reviews = $this->reviews;
 
        return [
            'id' => $this->id,
            'name_ar' => $this->getTranslation('name', 'ar'),
            'name_en' => $this->getTranslation('name', 'en'),
			'item_no' => $this->item_no,
            'description_ar' => $this->getTranslation('description', 'ar'),
            'description_en' => $this->getTranslation('description', 'en'),
            'material_ar' => $this->getTranslation('material', 'ar'),
            'material_en' => $this->getTranslation('material', 'en'),
            'composition_ar' => $this->getTranslation('composition', 'ar'),
            'composition_en' => $this->getTranslation('composition', 'en'),
            'care_instructions_ar' => $this->getTranslation('care_instructions', 'ar'),
            'care_instructions_en' => $this->getTranslation('care_instructions', 'en'),
            'fit_ar' => $this->getTranslation('fit', 'ar'),
            'fit_en' => $this->getTranslation('fit', 'en'),
            'season_ar' => $this->getTranslation('season', 'ar'),
            'season_en' => $this->getTranslation('season', 'en'),
            'style_ar' => $this->getTranslation('style', 'ar'),
            'style_en' => $this->getTranslation('style', 'en'),
            'available' => $this->available,
            'slug' => $this->slug,
            'is_favourite' => $is_favourite,
			'section_id' => $this->category()->section->id,
			'category_id' =>  $this->category()->id,
			'sub_category_id' =>  $this->subCategory->id,
            'is_notified' => $is_notified,
            'product_sku_code' => $this->item_no,
            'season' => $this->season,
            'has_discount' => $has_discount,
            'discount' => $discount,
            'group' => $this->whenLoaded('group'),
            'product_variations' => $product_variations->groupBy('color')->map(function ($items, $color) {
                $color = Color::make(json_decode($color, true));
                $locale = app()->getLocale();
                return [
                    'color_id' => $color->id,
					'sku_code' => $color->sku_code,
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
