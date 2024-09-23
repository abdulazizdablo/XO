<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoShipmentPV extends Model
{
    use HasFactory;

    protected $table = 'shipment_product_variations';


    protected $fillable = [

        'cargo_shipment_id',
        'product_variation_id',
        'quantity',
        'recieved'


    ];
    protected $casts = [
        'shipped_date' => 'datetime:Y-m-d H:i:s',
        'delivery_date' => 'datetime:Y-m-d H:i:s',
        'received_date' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];


    public function product_variation()
    {


        return $this->belongsTo(ProductVariation::class);
    }

    public function deliveredExpectedScope($query)
    {
        return $query->cargo_requests_pv()->whereColumn('requested_from_manager', '!=', 'requested_from_inventory')->count();
    }
}
