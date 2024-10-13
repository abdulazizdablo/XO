<?php

namespace App\Models;

use App\Traits\DateScope;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Comments;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use App\Traits\TranslateFields;
use Illuminate\Database\Eloquent\Builder;
use Umutphp\LaravelModelRecommendation\InteractWithRecommendation;
use Umutphp\LaravelModelRecommendation\HasRecommendation;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
// use \Znck\Eloquent\Traits\BelongsToThrough;

class Product extends Model implements InteractWithRecommendation
{
    use HasSlug;
    use HasRecommendation;
    use HasTranslations, TranslateFields;
    use HasFactory, SoftDeletes;
    use DateScope;
    use HasRelationships;

    public $translatable = [
        'name',
        'description',
        'material',
        'composition',
        'care_instructions',
        'fit',
        'style',
        'season',
    ];

    protected $fillable = [
        'discount_id',
        'group_id',
        'sub_category_id',
        'available',
        'item_no',
        'slug',
        'name',
        'description',
        'material',
        'composition',
        'care_instructions',
        'fit',
        'style',
        'season',
		'isNew'
    ];

    protected $casts = [
        'available' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

   public function orders()
    {
        return $this->hasManyDeep(
            Order::class,
            [ProductVariation::class, OrderItem::class], // Intermediate models, beginning at the far parent (Product).
            [
               'product_id', // Foreign key on the "product_variations" table.
               'product_variation_id',    // Foreign key on the "order_items" table.
               'id'     // Foreign key on the "orders" table.
            ],
            [
              'id', // Local key on the "products" table.
              'id', // Local key on the "product_variations" table.
              'id'  // Local key on the "order_items" table.
            ]
        );
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class)->where('valid',1);
    }

    public function product_variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function stocks()
    {
        return $this->hasManyThrough(
            StockLevel::class,
            ProductVariation::class,
            'product_id',
            'product_variation_id'
        );
    }

    public function order_items()
    {
        return $this->hasManyDeep(
            'App\Models\OrderItem',
            [
                // 'App\Models\StockLevel',// 'stock_level_id',
                'App\Models\ProductVariation',// 'product_variation_id',
            ],
            [
                null, 'product_variation_id', 'id'
            ]
        );
    }


    public function inventories()
    {
        return $this->hasManyDeep(
            'App\Models\Inventory',
            [
                'App\Models\ProductVariation',// 'product_variation_id',
                'App\Models\StockLevel',// 'stock_level_id',
            ],
            [
                null, null, 'inventory_id', 'id'
            ]
        );
    }

    public function photos()
	{
        return $this->hasMany(Photo::class);
    } 

    public function main_photos()
    {
        return $this->hasMany(Photo::class)
            ->where('main_photo', '1');
    }

    public function photosByColorId($colorIds)
    {
		  $photos = $this->hasMany(Photo::class)
        ->whereIn('color_id', $colorIds)
        ->get();

    if ($photos->isEmpty()) {
        // Return a default photo if no matching photos found
   $photos = collect([
            new Photo([
                'id' => 295,
                'product_id' => 4,
                'color_id' => 48,
                'thumbnail' => "https://api.xo-textile.sy/public/images/xo-logo.webp",
                'path' =>"https://api.xo-textile.sy/public/images/xo-logo.webp",
                'main_photo' => 1,
                'deleted_at' => null,
                'created_at' => "2024-01-10T19:57:56.000000Z",
                'updated_at' => "2024-01-10T19:57:56.000000Z"
            ]),
        ]);

// Simulate finding the photo in the database
  // $photos = $photos->newModelInstance($photos->getAttributes());
		
		return $photos;
   //	$photos = 	collect($photos->toArray());
    }

    return $photos;
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    public function averageRating()
    {
        return $this->reviews()
            ->selectRaw('avg(rating) as rating_avg')
            ->value('rating_avg');
    }

    public function transfer_item()
    {
        return $this->belongsTo(TransferItem::class);
    }

    public function commentedUsers()
    {
        return $this->belongsToMany(User::class, 'comments');
    }

    // Changed after attatching pricing with product
    public function pricing()
    {
        return $this->hasOne(Pricing::class);
    }

    // public function pricingByLocale()
    // {
    //     return $this->hasMany(Pricing::class)
    //         ->where('location', config('app.location'))
    //         ->first();
    // }

    public function colors()
    {
        return $this->hasManyThrough(
            Color::class,
            ProductVariation::class,
            'product_id', // foreign key on product_variations table
            'id', // local key on colors table
            'id', // local key on product_variations table
            'color_id' // foreign key on product_variations table
        );
    }

    public function sizes()
    {
        return $this->hasManyThrough(
            Size::class,
            ProductVariation::class,
            'product_id', // foreign key on product_variations table
            'id', // local key on colors table
            'id', // local key on product_variations table
            'size_id' // foreign key on product_variations table
        );
    }

    // changed after attatching favorite with product variation
    public function favourites()
    {
        return $this->belongsToMany(User::class, 'favourites');
    }

    public function notified()
    {
        return $this->belongsToMany(User::class, 'notifies');
    }

    public function comments()
    {
        return $this->hasMany(Comments::class);
    }

    public function variations()
    {
        return $this->belongsToMany(Variation::class, 'product_variations');
    }


    public function scopeNameContains($query, $keyword, $lang = 'en')
    {
        // $locale = app()->getLocale();
        $locale = $lang;

        return $query->where(function ($query) use ($locale, $keyword) {

            $query->where("name->" . $locale, "like", "%$keyword%");
            //   ->orWhere("description->".$locale, "like", "%$keyword%");
        });
    }

    public function scopeAvailable($query)
    {
        $query->where('available', 1);
    }

    public function isAvailable()
    {
        return $this->available == 1 ? true : false;
    }

    public function groupOffers()
    {
        return $this->hasMany(GroupOffer::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class)->where('valid',1);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'last_viewed');
    }

    // public function groups()
    // {
    //     return $this->belongsToMany(Group::class, 'group_products');
    // }


    public static function getRecommendationConfig(): array
    {
        return [

            'sold_together' => [
                'recommendation_algorithm'         => 'db_relation',
                'recommendation_data_table'        => 'products',
                'recommendation_data_table_filter' => [],
                'recommendation_data_field'        => 'id',
                'recommendation_data_field_type'   => self::class,
                'recommendation_group_field'       => 'sub_category_id',
                'recommendation_count'             => 3,
                // 'recommendation_order'             => 'random'
            ],


        ];
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
	
	public function category()
    {
        return $this->subCategory->category;
    }
}
