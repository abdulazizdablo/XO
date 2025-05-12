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
		'polygon',
		'lat',
		'long'
	];

	/*protected $casts = [
		'polygon' => 'string', // حفظ POLYGON كسلسلة نصية
	];*/

	public function stock_levels(){
		return $this->hasMany(StockLevel::class);
	}

	public function stock_movments(){
		return $this->hasMany(StockMovement::class);
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


    /**
     * إرجاع المستودع حسب الإحداثيات (باستخدام JSON فقط)
     */
    public static function findInventoryByLocation($latitude, $longitude)
    {
        $inventories = Inventory::all();
        
        $nearestInventory = null;
        $minDistance = INF;

        foreach ($inventories as $inventory) {
            $region = json_decode($inventory->region);
            foreach ($region as $coordinate) {
                $distance = self::calculateDistance($latitude, $longitude, $coordinate[1], $coordinate[0]);
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearestInventory = $inventory;
                }
            }
        }

        return $nearestInventory;
    }

    /**
     * البحث عن أقرب مستودع متاح باستخدام خوارزمية Haversine
     */
    public static function findNearestInventory($latitude, $longitude)
    {
        $inventories = Inventory::all();

        $nearestInventory = null;
        $minDistance = INF;

        foreach ($inventories as $inventory) {
            $region = json_decode($inventory->region);
            foreach ($region as $coordinate) {
                $distance = self::calculateDistance($latitude, $longitude, $coordinate[1], $coordinate[0]);
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearestInventory = $inventory;
                }
            }
        }

        return $nearestInventory;
    }

    /**
     * حساب المسافة بين نقطتين باستخدام خوارزمية Haversine
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // نصف قطر الأرض بالمتر

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }


}
