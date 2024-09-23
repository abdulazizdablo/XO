<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory;

class CargoRequest extends Model
{
    use HasFactory;


    protected $fillable = ['request_id','request_date', 'to_inventory', 'request_status_id', 'status', 'recieved_packages', 'employee_id','status'];

    public function cargo_requests_pv()
    {
        return $this->hasMany(CargoRequestPV::class);
    }
    
    protected $casts = [
        'shipped_date' => 'datetime:Y-m-d H:i:s',
        'delivery_date' => 'datetime:Y-m-d H:i:s',
        'received_date' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    
    public function inventory()
    {

        return $this->belongsTo(Inventory::class,'to_inventory');
    }
public function cargo_request_pv(){


    return $this->hasMany(CargoRequestPV::class);
}
    
    public function deliveredExpectedScope($query)
    {
        return $query->cargo_requests_pv()->whereColumn('requested_from_manager', '!=', 'requested_from_inventory')->count();
    }
}
