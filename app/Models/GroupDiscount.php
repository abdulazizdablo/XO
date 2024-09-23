<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'discount_id',
        'valid',
    ];

    public function groups()
    {
        return $this->belongsTo(Group::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

}
