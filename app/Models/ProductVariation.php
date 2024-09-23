<?php

namespace App\Models;

use App\Traits\TranslateFields;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Staudenmeir\EloquentHasManyDeep\HasRelationships;
// use \Znck\Eloquent\Traits\BelongsToThrough;

class ProductVariation extends Model
{
    // use BelongsToThrough;
    use HasRelationships;
    use TranslateFields;
    use HasFactory, SoftDeletes;

    
    public $translatable = [
        'name'
    ];
    protected $fillable = [
        'group_id',
        'product_id',
        'variation_id',
        'color_id',
        'size_id',
        'sku_code',
        'visible',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }



    public function group()
    {
        return $this->belongsTo(Group::class)->where('valid',1);
    }

    public function inventories()
    {
        return $this->hasManyThrough(
            Inventory::class,
            StockLevel::class,
            'product_variation_id', // foreign key on product_variations table
            'id', // local key on colors table
            'id', // local key on product_variations table
            'inventory_id' // foreign key on product_variations table
        );
    }

    public function stock_levels()
    {
        return $this->hasMany(StockLevel::class);
    }

    public function stock_movements()
    {
        return $this->belongsToMany(StockMovement::class, 'stock_variations');
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // changed after attatching favorite with product variation
    public function notifies()
    {
        return $this->belongsToMany(User::class, 'notifies');
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // public function photos()
    // {
    //     return $this->hasMany(Photo::class, 'color_id');
    // }

    public function photos()
    {
        return $this->hasManyThrough(Photo::class, ProductVariation::class, 'color_id', 'product_id');
    }

    public function categoryOrders()
    {
        return $this->hasManyDeep(
            'App\Models\Order',
            [
                'sub_category_id',
                'product_id',
                'product_variation_id',
                'order_id',
                'id'
            ],
            [
                null, null, null, null, 'order_id'
            ]
        );
    }
	
	/*public function getPhotosAttribute()
	{
		$photos = $this->getRelationValue('photos');

		if ($photos && $photos->isNotEmpty()) {
			return $photos;
		} else {
			return collect([ 
							"id" => 1,
							"product_id" => $this->attributes['product']->id,
							"color_id" => $this->attributes['color_id'],
							"path" => "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/products/20?_a=E",
							"main_photo" => 1]);
		}
	}*/
	
	
}
