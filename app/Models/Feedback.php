<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'content',
        'rate',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function order(){
        return $this->hasOne(Order::class);
    }

}
