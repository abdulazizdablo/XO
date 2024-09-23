<?php

namespace App\Http\Resources;

use App\Traits\TranslateFields;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Photo;
use App\Models\Color;
use App\Models\Size;

class OrderItemResource extends JsonResource
{

    use TranslateFields;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $product_variation = ProductVariationResource::make($this->product_variation);
            // return  $product_variation;
        $product_fields =[
            'name',
            'description',
            'material',
            'composition',
            'fabric',
            'care_instructions',
            'fit',
            'style',
            'season',

        ];

        $color_fields =[
            'value',
            'hex_code',

        ];
		$product_variations= $product_variation->product->product_variations;
		$size_ids = $product_variations->flatten()->pluck('size_id')->unique();
		$sizes = Size::whereIn('id', $size_ids)->get();
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
			'status' => $this->status,
            // 'product_name' => $this->whenLoaded('product_variation'),
            'product_variation' => $product_variation,
			'product_variations' => ((new ProductVariationCollection(
                    $product_variation->product->product_variations,
                   // collect($this->notified_products)->pluck('product_variations.*.id') ?? null,
                    // collect($this->favourites_products)->pluck('product_variations.*.id')
                ))->values())->groupBy('color') // Grouping the variations by the 'color' attribute
                        ->map(function ($item2, $color) {
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
                        ->toArray(),
			'sizes' => $sizes->map(function ($item2) {
                        return [
                            "id" => $item2->id,
                            "value" => $item2->value,
                            "type" => $item2->type,

                        ];
                    }),
            'product' => $product_variation->product->getFields($product_fields),
            'color' => $this->product_variation->color->getFields($color_fields),
            'photo' => $product_variation->product->photos,
			'order_photo' => Photo::where([['product_id', $product_variation->product->id],['color_id',$product_variation->color_id]])->first()->path??"https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/2024-04-29_110732?_a=E",
            'variations'=> [
                            'colors' =>$product_variation->product->colors,
                            'sizes' =>$product_variation->product->sizes
                        ],
                    // 'product_id' => $item->product->id,
                    // 'name' => $item->product->name,
                    // 'color' => $item->color,
                    // 'size' => $item->size,

        ];
		
		
		var_dump($product_variation);
    }
}
