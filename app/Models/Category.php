<?php

namespace App\Models;

use App\Traits\TranslateFields;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Category extends Model
{
    use HasSlug;
    // use Sluggable;
    use HasRelationships;
    use HasTranslations, TranslateFields;
    use HasFactory, SoftDeletes;

    public $translatable = ['name'];

    protected $fillable = [
        'section_id',
        'name',
        'slug',
        'photo_url',
        'thumbnail',
        'valid',
    ];

    public function categoryOrders()
    {
        return $this->hasManyDeep(
            'App\Models\Order',
            [
                'App\Models\SubCategory',// 'sub_category_id',
                'App\Models\Product',// 'product_id',
                'App\Models\ProductVariation',// 'product_variation_id',
                'App\Models\OrderItem',// 'order_item_id',
            ],
            [
                null, null, null, null, 'id'
            ]
        );
    }


    public function orders()
    {
        return $this->hasManyDeep('App\Models\OrderItem',
        ['App\Models\SubCategory',
        'App\Models\Product',
        'App\Models\ProductVariation' ]);
    }


    public function subCategories(){
        return $this->hasMany(SubCategory::class);
    }

    public function products(){
        return $this->hasManyThrough(Product::class, SubCategory::class);
    }

    public function section(){
        return $this->belongsTo(Section::class);
    }

    // public function orders()
    // {
    //     return $this->hasManyDeep('App\Models\OrderItem', ['App\Models\SubCategory', 'App\Models\Product','App\Models\ProductVariation' ]);
    // }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function ($model){
                return $this->getTranslation('name', 'en');
            })
            ->saveSlugsTo('slug');
    }

    public function scopeValid($query)
    {
        return $query->where('valid', 1);
    }

        /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

}
