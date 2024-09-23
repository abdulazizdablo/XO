<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TranslateFields;

class Pricing extends Model
{
    use HasTranslations, TranslateFields;
    use HasFactory, SoftDeletes;

    protected $translatable = ['name'];

    protected $fillable = [
        'product_id',
        'location',
        'name',
        'currency',
        'value',
        'valid',
    ];

    // Changed after attatching pricing with product
    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function scopeValid($query)
    {
        $query->where('valid', 1);
    }

}
