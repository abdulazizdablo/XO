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
		'exchange_id',
		'refund_id',
        'gift_id',
        'user_id',
		//'refunded_amount',
        'amount',
        'transaction_uuid',
        'status',
        'payment_method',
		'transaction_source',
		'operation_type'

    ];
	
	    protected $casts = [
          'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
			'amount' => 'integer'
    ];

    public function order (){
        return $this->belongsTo(Order::class);
    }
	

	

    public function inventory()
    {
        return $this->hasOneThrough(
            Inventory::class,  // Final model
            Order::class,      // Intermediate model
            'id',              // Foreign key on the orders table
            'id',              // Foreign key on the inventories table
            'order_id',        // Local key on the transactions table
            'inventory_id'     // Local key on the orders table
        );
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
