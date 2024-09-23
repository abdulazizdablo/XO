<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Exchange extends Model implements Auditable
{
    use HasFactory;
 	use \OwenIt\Auditing\Auditable;
    
	protected $fillable = [
        'order_id', 'quantity','exchange_date','total_exchange','status','reason'
    ];

    public function exchange_items()
    {
        return $this->hasMany(ExchangeItem::class);
    }
	
	public function order(){
        return $this->belongsTo(Order::class);
    }
}
