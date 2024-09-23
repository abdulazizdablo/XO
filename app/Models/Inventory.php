<?php

namespace App\Models;

// use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Traits\TranslateFields;
use LaracraftTech\LaravelDateScopes\DateScopes;
use App\Traits\DateScope;
use Spatie\Translatable\HasTranslations;
use App\Traits\TranslateFields;

class Inventory extends Model
{
    use \Znck\Eloquent\Traits\BelongsToThrough;
    // use HasTranslations ,TranslateFields;
    use HasFactory, SoftDeletes;
    use DateScopes;
    use DateScope;

    use HasTranslations, TranslateFields;

    public $translatable = [
        'city',
    ];

    protected $fillable = [
        'name',
        'code',
        'city',
        'city_id',
        'lat',
        'long'
    ];

    public function stock_levels(){
        return $this->hasMany(StockLevel::class);
    }

    public function stock_movments(){
        return $this->hasMany(StockMovement::class);
    }

    public function transfers(){
        return $this->hasMany(Transfer::class);
    }

    public function employees(){
        return $this->hasMany(Employee::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }
    
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function products()
    {
        return $this->hasManyDeep(
            Product::class,
            [ StockLevel::class, ProductVariation::class],
            [null, 'id', 'id','id'],
            [null, 'inventory_id', 'product_variation_id' ,'product_id']
        );
    }

}
