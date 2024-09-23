<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaracraftTech\LaravelDateScopes\DateScopes;
use App\Traits\DateScope;

class StockMovement extends Model
{
    use HasFactory, SoftDeletes;
    use DateScopes;
    use DateScope;


    protected $fillable = [
        // 'product_variation_id',
        'from_inventory_id',
        'shipment_name',
        'to_inventory_id',
        'num_of_packages',
        'status',
        'delivery_date',
        'shipped_date',
        'received_date',
        'expected',
        'received',
    ];

    protected $casts = [
        'shipped_date' => 'datetime:Y-m-d H:i:s',
        'delivery_date' => 'datetime:Y-m-d H:i:s',
        'received_date' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function source_inventory()
    {
        return $this->belongsTo(Inventory::class, 'from_inventory_id');
    }

    public function destination_inventory()
    {
        return $this->belongsTo(Inventory::class, 'to_inventory_id');
    }

    public function product_variations()
    {
        // return $this->belongsTo(ProductVariation::class, 'product_variation_id');
        return $this->belongsToMany(ProductVariation::class, 'stock_variations');
    }


}
