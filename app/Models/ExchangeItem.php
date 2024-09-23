<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ExchangeItem extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'exhange_items';

    protected $fillable = [
        //'order_item_id',
        'product_variation_id',
        'returned_product_variation_id',
        'exchange_id',   
        'in_quantity',   
        'out_quantity'   
    ];
	
	public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function returnedProductVariation()
    {
        return $this->belongsTo(ProductVariation::class, 'returned_product_variation_id');
    }
}
