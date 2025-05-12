<?php

namespace App\Models;

use App\Traits\TranslateFields;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;

class SubCategory extends Model
{
    use HasTranslations, HasTranslatableSlug, TranslateFields;
    use HasFactory, SoftDeletes;

    public $translatable = [
        'name',
        'slug',
    ];

    protected $fillable = [
        'category_id',
        'name',
        //'photo_url',
        //'thumbnail',
        'valid',
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function products_variations(){
        return $this->hasManyThrough(ProductVariation::class, Product::class);
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::createWithLocales(['en'])
            ->generateSlugsFrom(function($model, $locale) {
                return "{$model->name}";
            })
            ->saveSlugsTo('slug');
    }


    public function scopeValid($query)
    {
        $query->where('valid', 1);
    }

}
