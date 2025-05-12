<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use App\Traits\TranslateFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Group extends Model
{
    use HasSlug;
    use HasFactory;
	use SoftDeletes;
    use HasRelationships;
    use HasTranslations, TranslateFields;

    public $translatable = [
        'expired', 'name'
    ];

    protected $fillable = [
        'type',
        'promotion_type',
        'name',
        'valid',
        'expired',
        'percentage' , 
        'number_of_items',
        'image_path',
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    // public function products()
    // {
    //     return $this->belongsToMany(Product::class, ProductVariation::class, 'group_id', 'product_id');
    // }

    public function photos()
    {
        return $this->hasManyDeep(Photo::class, [ProductVariation::class, 'colors']);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function productVariations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function discounts()
    {
        return $this->hasOne(Discount::class);
    }


    public function offers()
    {
        return $this->hasOne(Offer::class);
    }

  
    public function scopeValid($query)
    {
        return $query->where('valid', 1);
    }

    public function scopeFlash($query)
    {
        return $query->where('promotion_type', 'flash');
    }

    public function scopeOffer($query)
    {
        return $query->where('type', 'offer');
    }

    public function scopeDiscount($query)
    {
        return $query->where('type', 'discount');
    }

    
    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function ($model) {
                return $this->getTranslation('name', 'en');
            })
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
