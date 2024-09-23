<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'type',
        'date',
        'time',
        'city',
        'street',
        'neighborhood',
        'lat',
        'long',
        'additional_details',
        'receiver_first_name',
		'receiver_father_name',
        'receiver_last_name',
        'receiver_phone',
        'receiver_phone2',
        'is_delivered',
        'city_id',
        'express',
    ];
	
	
	
	
    
}
