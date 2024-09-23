<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'offer_id',
        'valid',
    ];

    public function groups()
    {
        return $this->belongsTo(Group::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeValid($query)
    {
        return $query->where("valid", 1);
    }
}
