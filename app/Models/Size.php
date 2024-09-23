<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TranslateFields;
use Spatie\Translatable\HasTranslations;

class Size extends Model
{
    use HasFactory, HasTranslations ,TranslateFields;

    public $translatable = [
        'value'
    ];

    protected $fillable = [
        'value',
        'sku_code',
        'type'
    ];

}
