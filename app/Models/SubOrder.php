<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubOrder extends Model
{
    use HasTranslations;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'packaging_id',
        'order_item_id',
    ];
}
