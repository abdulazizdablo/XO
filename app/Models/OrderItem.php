<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use LaracraftTech\LaravelDateScopes\DateScopes;
use App\Traits\DateScope;
use OwenIt\Auditing\Contracts\Auditable;

class OrderItem extends Model implements Auditable
{
    // use HasTranslations;
    use HasRelationships;
    use HasFactory, SoftDeletes;
    use DateScopes ;
    use DateScope;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'order_id',
        'product_variation_id',
        'return_order_id',
        'quantity',
		'original_inventory',
        'to_inventory',
        'final_date',
        'price',
        'on_hold',
        'original_price',
        'status',
        'group_id',
        'promotion_name'
    ];

    public function order(){
        return $this->belongsTo(Order::class);
}   


    public function product_variation(){
        return $this->belongsTo(ProductVariation::class);
    }
	
	public function getItemNoAttribute(){
		//return $this->product_variation->product->item_no;
		return $this->product_variation->product()->first()->item_no;
				
	}

    public function getNameAttribute(){
		return $this->product_variation->product()->first()->slug;
				
	}
    
    

}
