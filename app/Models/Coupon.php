<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use OwenIt\Auditing\Contracts\Auditable;


class Coupon extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;


    protected $fillable = [
        'user_id',
        'type',
        'name',
        'code',
        'password',
        'valid',
        'used_redemption',
        'max_redemption',
        'amount_off',
        'status',
        'percentage',
        'expired_at',
        'last_recharge',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'last_recharge' => 'datetime:Y-m-d H:i:s',
    ];

    protected $hidden = ['password'];

    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function giftOrders()
    {
        return $this->hasMany(Order::class, 'gift_id');
    }

    public function remainUsingCounts(){
        return  $this->max_redemption - $this-> used_redemption;
    }

    public function isValid(){
        return $this->valid == 1 ? true : false;
    }

    public function scopeValid($query){
        return $query->where("valid", 1)->where('status',1);
    }

    public function scopeGift($query){
        return $query->where("valid", 1);
    }

    // public function getEncryptedAmountOff($value)
    // {
    //     return Crypt::decrypt($value);
    // }

    protected static function boot()
    {
        parent::boot();
        Coupon::retrieved(function ($model) {
            if($model->amount_off != null){
                $model->amount_off=Crypt::decryptString($model->amount_off);}
      
        });
    }

}
