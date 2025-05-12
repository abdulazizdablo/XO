<?php

namespace App\Models;

use App\Traits\DateScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use OwenIt\Auditing\Contracts\Auditable;


class StockLevel extends Model implements Auditable
{
    use \Znck\Eloquent\Traits\BelongsToThrough;
    use HasRelationships;
    use HasFactory, SoftDeletes;
    use DateScope;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'product_variation_id',
        'inventory_id',
        'name',
        'min_stock_level',
        'max_stock_level',
        'current_stock_level',
        'on_hold',
		'shipment_hold',
        'target_date',
        'sold_quantity',
        'status',
    ];

    protected $casts = [
        'created_at' => 'date:Y/m/d, h:m:s',
        'updated_at' => 'date:Y/m/d, h:m:s',
        'target_date' => 'date:Y/m/d, h:m:s',
    ];
    public function audit(): array
    {
        return [
            'current_stock_level',
            // Add other attributes you want to audit
        ];
    }

    protected $appends = ['raise'];

    /**
     * Determine  full name of user
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function getRaiseAttribute()
    {
        return $this->current_stock_level <= $this->min_stock_level ? true : false;
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product_variation()
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function product()
    {
        return $this->belongsToThrough(Product::class, ProductVariation::class);
    }

}
