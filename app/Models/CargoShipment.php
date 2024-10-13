<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CargoRequest;
use LaracraftTech\LaravelDateScopes\DateScopes;
use App\Traits\DateScope;

class CargoShipment extends Model
{
    use HasFactory, DateScopes, DateScope;

    protected $table = 'cargo_shipments';


    protected $fillable = [
        'shipment_name',
        'status',
        'request_id',
        'cargo_request_id',
		'received_date',
        'ship_date',
        'from_inventory',
        'to_inventory',
        'sender_packages',
        'employee_id'

    ];


    protected $casts = [
        'shipped_date' => 'datetime:Y-m-d H:i:s',
        'delivery_date' => 'datetime:Y-m-d H:i:s',
        'received_date' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];


    /* protected static function boot()
       {
           parent::boot();

           static::creating(function ($model) {
               if ($model->cargo_request_id) {
                   $cargoRequest = CargoRequest::find($model->cargo_request_id);
                   if ($cargoRequest) {
                       $model->to_inventory = $cargoRequest->to_inventory;
                   }
               } 
               
               else
               
                $model->to_inventory =  $model->to_inventory;
               
               
           });
       }

   */
    public function cargo_shipment_pv()
    {


        return $this->hasMany(CargoShipmentPV::class);
    }

    public function cargo_request()
    {



        return $this->belongsTo(CargoRequest::class);
    }
    public function inventory()
    {


        return $this->belongsTo(Inventory::class, 'from_inventory');
    }


    public function to_inventory()
    {


        return $this->belongsTo(Inventory::class, 'to_inventory');
    }

}
