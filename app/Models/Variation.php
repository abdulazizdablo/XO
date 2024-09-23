<?php

namespace App\Models;

use App\Traits\TranslateFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Variation extends Model
{
    use HasTranslations, TranslateFields;
    use HasFactory;

    public $translatable = [
        'property',
        'value',
    ];

    protected $fillable = [
        'type',
        'property',
        'value',
        'hex_code',
        'main_color',
    ];

    protected $casts = [
        'main_color' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class,'product_variations');
    }

    public function product_variation(){
        return $this->belongsTo(ProductVariation::class);
    }



}
