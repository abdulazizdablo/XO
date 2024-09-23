<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoRequestPV extends Model
{
    use HasFactory;


    protected $table = 'request_product_variations';

    protected $guarded = [];

    protected $casts = [
        'shipped_date' => 'datetime:Y-m-d H:i:s',
        'delivery_date' => 'datetime:Y-m-d H:i:s',
        'received_date' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

 public function product_variation(){


        return $this->belongsTo(ProductVariation::class);
    }



  
}
