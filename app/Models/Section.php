<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TranslateFields;
use \Staudenmeir\EloquentHasManyDeep\HasRelationships;



class Section extends Model
{
    use HasTranslations,TranslateFields;
    use HasFactory, SoftDeletes;
    use HasRelationships;


    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'photo_url',
        'thumbnail',
    ];

    public function subCategories(){
        return $this->hasManyThrough(SubCategory::class ,Category::class);
    }

    public function categories(){
        return $this->hasMany(Category::class);
    }
    public function orders()
    {
        return $this->hasManyDeep('App\Models\OrderItem', ['App\Models\Category' , 'App\Models\SubCategory', 'App\Models\Product','App\Models\ProductVariation' ]);
    }

}
