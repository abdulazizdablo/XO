<?php

namespace App\Models;

//use App\Traits\TranslateFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Spatie\Translatable\HasTranslations;
class Setting extends Model
{
    use HasFactory;
    //use HasTranslations, TranslateFields;

  /*  public $translatable = [
        'value',
    ];
 */

    protected $fillable = [
        'key',
        'value',
    ];
/*protected $casts = [
	'value' => 'array'
	];*/
    public static function getSettingsByLanguage($languageCode)
    {
        return self::whereRaw("JSON_EXTRACT(value, '$.$languageCode') IS NOT NULL")
                    ->get()
                    ->map(function ($setting) use ($languageCode) {
                        // Manually extract the specific language value from the JSON
                        $setting->value = json_decode($setting->value)->$languageCode;
                        return $setting;
                    });
    }


    protected $hidden = ['created_at','updated_at'];
}
