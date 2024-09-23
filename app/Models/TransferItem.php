<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferItem extends Model
{
    use HasTranslations;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transfer_id',
        'quantity',
    ];

    public function transfer(){
        return $this->belongsTo(Transfer::class);
    }

    public function products(){
        return $this->hasMany(Product::class);
    }
}
