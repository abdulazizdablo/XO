<?php

namespace App\Models;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use App\Traits\DateScope;
use Laravel\Sanctum\HasApiTokens;
//use Spatie\Permission\Traits\HasRoles;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use LaracraftTech\LaravelDateScopes\DateScopes;

class User extends Authenticatable implements MustVerifyEmail
{
    use DateScopes;
    use HasRelationships;
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    //use HasRoles;
    use DateScope;

    // public $guard_name = 'api-employees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'last_name',
        'first_name',
        'email',
        'password',
        'address',
        'phone',
        'banned',
        'isVerified',
        'is_deleted',
		'lang'
        // 'banned_until'
    ];

    // protected $dates = [
    //     'banned_until'
    // ];

    protected $casts = [
        'created_at' => 'date:Y-m-d h:m:s',
        'updated_at' => 'date:Y-m-d h:m:s',
        'isVerified' => 'boolean' 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function histories()
    {
        return $this->hasMany(BanHistory::class);
    }

    public function fcm_tokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favourites_products()
    {
        return $this->belongsToMany(Product::class, 'favourites');
    }
	
	
	public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function notifies()
    {
        // return $this->belongsToMany(Product::class, 'favourites');
        return $this->belongsToMany(ProductVariation::class, 'notifies');
    }

    public function notified_products()
    {
        return $this->hasManyDeep(
            Product::class,
            [
                Notify::class,
                ProductVariation::class
            ],
            [null, 'id', 'id'],
            [null, 'product_variation_id', 'product_id']
        );
    }

    public function notified_product_variations()
    {
        return $this->hasManyDeep(
            ProductVariation::class,
            [
                Notify::class,
                Product::class
            ],
            [null, 'id', 'id'],
            [null, 'product_variation_id', 'id']
        );
    }

    public function commentedProducts()
    {
        return $this->belongsToMany(Product::class, 'comments');
    }
	
	
	
		public function getFullNameAttribute(){
	
		return $this->first_name . ' ' . $this->last_name; 
		
	}

	public function orders()
    {
        return $this->hasMany(Order::class)->where(function ($query) {
			$query->where('paid', 1)->orWhere([['payment_method', 'cod'],['paid', 0]]);
		});
    }
	
	/*public function orders()
    {
        return $this->hasMany(Order::class);
    }*/

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }
	
    public function gift_cards(){
    return $this->hasMany(Coupon::class)->where('type', 'gift');
   }

    public function items_images()
    {
        return $this->hasManyDeep(
            Photo::class,
            [
                Order::class,
                OrderItem::class,
                Sku::class,
                ProductVariation::class,
            ],
            [null, 'id', 'id', 'id', 'id'],
            [null, 'user_id',  'sku_id', 'product_variation_id']
        );
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function lastViewed()
    {
        return $this->belongsToMany(Product::class, 'last_viewed');
    }


    public function scopeBanned($query){
        $query->whereNotNull('banned_until');
    }
	
	
	
	public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }
	
}
