<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Refund;
use OwenIt\Auditing\Contracts\Auditable;


class Refund extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    
protected $fillable = [
    'order_id',
    'user_id',
    'inventory_id',
    'refund_date',
	'packed_date',
    'delivery_date',
    'shipping_date',
    'receiving_date',
    'total_refund',
    'status',
    'reason'
];


  public function refund_items()
    {
        return $this->hasOne(RefundItem::class);
    }

}
