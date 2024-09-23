<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TranslateFields;
use Spatie\Translatable\HasTranslations;

class Color extends Model
{
    use HasFactory, HasTranslations ,TranslateFields;

    public $translatable = [
        'name'
    ];

    protected $fillable = [
        'id',
        'name',
        'hex_code',
        'sku_code',
    ];

    public function photos(){
        return $this->hasMany(Photo::class);
    }

}
