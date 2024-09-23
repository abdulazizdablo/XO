<?php

namespace App\Http\Resources;

use App\Models\Discount;
use App\Models\User;
use App\Models\StockLevel;
use App\Traits\TranslateFields;
use Illuminate\Http\Resources\Json\JsonResource;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\Log;

class ProductVariationResource extends JsonResource
{
    use TranslateFields;

    public $preserveKeys = true;
    // protected $notified_products;
//protected $user;
    //protected $notified_products_ids;
    public function __construct($resource/*,protected User  $user,protected $notified_products_ids*/)
    {
        parent::__construct($resource);
        //$this->additionalData = $additionalData;
        // dd( $user);
        // $this->notified_products_ids = $notified_products_ids;
        //$this->resource = $this->collect($resource) ? $resource : null;
        // $this->user = $user;

    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        // dd($this->$request->user);
		$employee = auth('api-employees')->user();
        if($employee){
			$user= null;
			$is_notified = false;
		}else{
			$user = auth('sanctum')->user();
			$is_notified = false;
			if ($user) {
				$user->load(['favourites_products', 'notifies', 'reviews', 'orders']);
				$is_notified = collect($user->notifies)->contains(function ($notify) {
					return $notify->id === $this->id;
				});
				// Access relationships here
			} else {
				$user= null;
			}    	
		}
      
     
   
        // $user = User::find($user_id)->load(['favourites_products', 'notified_products']);
        // dd($notified_products);
        $quantity = 0;
		$size_quantity = 0;
        $status = 'Out of Stock';
        $max_quantity_per_order = 0;
        // $is_notified = $user->notified_product_variations->contains($this->id);

       // $is_notified = collect($user->notifies)->contains($this->id);

        $has_offer = $this->has('group') ?? null;
        if ($has_offer) {
            $offer = 2;
        }
/*$product_variations = ProductVariation::with('stock_levels')->where('product_id',$this->product_id)->where('size_id',$this->size_id)->get();
	
		$product_variations->each(function($item) use(&$size_quantity){
		$size_quantity+= $item->stock_levels->sum('current_stock_level');
		
		});
*/
        $has_stock_levels = collect($this->stock_levels);
        if ($has_stock_levels) {
            $quantity = $this->stock_levels->sum('current_stock_level');

             //$status = 'Out of Stock';
        }

        //dd($this->stock_levels);

        if ($has_stock_levels && $quantity == 0) {
            $status = 'Out of Stock';
        } elseif ($has_stock_levels && $quantity < 4) {
            $status = 'Last few Items';
            $max_quantity_per_order = 1;
        } elseif ($has_stock_levels && $quantity >= 4) {
            $status = 'In Stock';
            $max_quantity_per_order = ceil($quantity * 2 / 100);
        }

        return [
            'product_variation_id' => $this->id,
            'color_id' => $this->color_id,
            'quantity' =>StockLevel::where('product_variation_id', $this->id)->max('current_stock_level'),
            'max_quantity_per_order' => 10,
            'status' => $status,
            'sku_code' => $this->sku_code,
            'offer' => [
                    'expiration_date' => $offer,
                ],
            'size' => [
				'id' => $this->size->id,
				'sku_code' => (int)$this->size->sku_code,
                'notify' => $is_notified,
                'can_be_notified' =>  !$has_stock_levels||$quantity == 0,
                'value' => $this->size->value,
            ],

        ];

    }



    /*  public static function collectionWithAdditionalData($collection,$user, $additionalData)
      {
          return $collection->map(function ($item) use ($additionalData) {
              return (new self($item))->addAdditionalData($additionalData);
          });

         self::$user = $user;
      }
  */
    /*public function addAdditionalData($additionalData)
    {
        // Modify the resource to include the additional data
        $this->user = $additionalData;
        return $this;
    }
    */
}
