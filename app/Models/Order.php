<?php

namespace App\Models;

use App\Traits\DateScope;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use LaracraftTech\LaravelDateScopes\DateScopes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Scopes\AdjustCreatedAtScope;

class Order extends Model implements Auditable
{
    use DateScopes;
    // use HasTranslations;
    use HasRelationships;
    use HasFactory, SoftDeletes;
    use DateScope;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'user_id',
        'inventory_id',
        'packaging_id',
		'original_order_id',
        'coupon_id',
        'gift_id',
        'employee_id',
        'address_id',
        'invoice_number',
        'packed_date',
		'delivery_date',
        'shipping_date',
        'receiving_date',
		'price_without_offers',
        'total_price',
        'paid_by_user',
        'covered_by_gift_card',
        'discounted_by_coupon',
        'total_quantity',
		'is_gift',
		'gift_message',
        'paid',
        'closed',
        'status',
		'type',
		'branch_id',
		'delivery_type',
        'payment_method',
        'need_packaging',
        'shipping_fee',
        'qr_code',
    ];

    protected $casts = [
          'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];
	/*
	protected static function booted()
    {
        static::addGlobalScope(new AdjustCreatedAtScope);
    }
*/
	
	 protected function getCreatedAtAttribute()
    {
		 return \Carbon\Carbon::parse($this->attributes['created_at'])/*->addHours(3)*/->format('Y-m-d H:i:s');
    }
	
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function gift()
    {
        return $this->belongsTo(Coupon::class, 'gift_id');
    }
	
	public function invoice(){
		return $this->hasOne(Invoice::class);	
	}

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }
	
	public function exchanges(){
        return $this->hasMany(Exchange::class);
    }
	
	public function refunds(){
        return $this->hasMany(Refund::class);
    }
    
	    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
	
    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function address(){
        return $this->belongsTo(Address::class);
    }

    public function order_items(){
        return $this->hasMany(OrderItem::class);
    }

    public function transaction(){
        return $this->hasOne(Transaction::class);
    }

    public function inventory(){
        return $this->belongsTo(Inventory::class);
    }

    public function shipment(){
        return $this->hasOne(Shipment::class);
    }

    public function scopeClosed($query)
    {
        $query->where('closed', 1);
    }

    public function scopeOpened($query)
    {
        return $query->where('closed', 0);
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }

    public function scopePaid($query)
    {
        return $query->where('paid', 1);
    }
	
	public function getDiscountAttribute()
    {
        //return $this->price_without_offers - $this->total_price;
        return $this->discounted_by_coupon;
    }
	
	public function getPriceWithFeesAttribute()
    {
		//if($this->payment_method == 'cod'){
		//        return $this->paid_by_user + $this->shipping_fee;
		//}
		return $this->total_price + $this->shipping_fee - $this->discounted_by_coupon;
        return $this->total_price + $this->shipping_fee;
		// return $this->paid_by_user + $this->shipping_fee;
    }
	
	public function getReplaceOrderPriceDifferenceWithFeesAttribute(){
		$order_items = $this->order_items;
		$newSum = 0;
		$returnSum = 0;

		foreach ($order_items as $item) {
			if ($item->status == 'new') {
				$newSum += $item->price;
				//$newSum += $item->original_price;
			} elseif ($item->status == 'return') {
				$returnSum += $item->price;
				//$returnSum += $item->original_price;
			}
		}
		return $newSum - $returnSum + $this->shipping_fee;
	}

    public function getNewItemsPriceAttribute(){
        $order_items = $this->order_items;
        $returnSum = 0;

		foreach ($order_items as $item) {
			if ($item->status == 'new') {
				$returnSum += $item->original_price;
			}
		}

		return $returnSum;
    }
		
	public function getReturnOrderPriceAttribute(){
		$order_items = $this->order_items;
		$returnSum = 0;

		foreach ($order_items as $item) {
			if ($item->status == 'return') {
				$returnSum += $item->original_price;
			}
		}

		return $returnSum;
	}
	
	public function getReturnOrderPriceMinusFeesAttribute(){
		return $this->returnOrderPrice - $this->shipping_fee;
	}
	
	public function getReasonAttribute(){
		return $this->exchanges()->first()->reason ?? $this->refunds()->first()->reason ?? null ;
	}
	
	public function getUserFullNameAttribute(){
	
		return $this->user->first_name . ' ' . $this->user->last_name; 
		
	}
    
    protected static function boot()
    {
    parent::boot();

    static::created(function ($order) {
        // Generate the invoice number
        $invoiceNumber = 'TW_' . str_pad($order->id,  6, '0', STR_PAD_LEFT);

        // Update the invoice number in the database
        $order->update(['invoice_number' => $invoiceNumber]);
    });
}
	/*
	public function getCreatedAtAttribute()
	{
		return $this->attributes['created_at'];
	}
	*/
		
/*
	public function getTotalPriceAttribute()
	{
		if ($this->coupon_id === null) {
			return $this->attributes['total_price'];
		} else {
			return $this->attributes['total_price'] - $this->attributes['discounted_by_coupon'];
		}
	}*/
	
	 /*public function getPaymentMethodAttribute($value)
    {
        $paymentMethods = [
            'cod' => 'Cash On Delivery',
            'syriatel-cash' => 'Syriatel cash',
            'mtn-cash' => 'MTN cash',
            // Add other payment methods if needed
        ];

        return $paymentMethods[$value] ?? $value;
    }*/

}
