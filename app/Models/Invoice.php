<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Invoice extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'order_id',
        'user_id',	
        'shipment_id',	
        'total_price',	
        'fees',	
        'total_payment',	
        'invoice_number',
        'type',	
        'order_items' 
    ];

    protected $casts = [
        'order_items' => 'array',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function shipment(){
        return $this->belongsTo(Shipment::class);
    }

    public function order(){
        return $this->belongsTo(Order::class);
    }
    
}
