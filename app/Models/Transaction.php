<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaracraftTech\LaravelDateScopes\DateScopes;
use App\Traits\DateScope;
use OwenIt\Auditing\Contracts\Auditable;

class Transaction extends Model implements Auditable
{
    use HasFactory, SoftDeletes,DateScope,DateScopes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'order_id',
        'gift_id',
        'user_id',
        'amount',
        'transaction_uuid',
        'status',
        'payment_method',
		'operation_type'

    ];

    public function order (){
        return $this->belongsTo(Order::class);
    }
	
	public function gift_card(){
	
	return $this->belongsTo(Coupon::class)->where('type','gift');
	}
	
	public function user(){
		
		return $this->belongsTo(User::class);
	
	}
	
	public function exchange(){
		
		return $this->belongsTo(Exchange::class);
	
	}
	
	
		public function refund(){
		
		return $this->belongsTo(Refund::class);
	
	}


}
