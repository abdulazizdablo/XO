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
	
	
  /*  protected static function booted()
    {
        static::retrieved(function ($price) {
            // Transform attributes here
            $price->value = (int)$price->value ;
         //   $app_translate->destination_language_content = json_decode($app_translate->destination_language_content, true);  // Example: Convert `name` to an integer
        });
    }*/
    // Changed after attatching pricing with product
    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function scopeValid($query)
    {
        $query->where('valid', 1);
    }

}
