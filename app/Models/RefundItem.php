<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RefundItem extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

protected $fillable = [
    'refund_id',
    'order_item_id',
    'product_variation_id',
    'quantity',
    'price'
   
];


   
}
