<?php

namespace App\Models;

use App\Traits\TranslateFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasFactory;
    use HasTranslations, TranslateFields;

    public $translatable = [
        'name',
    ];

    protected $fillable = [
        'name'
    ];

    public function branches(){
        return $this->hasMany(Branch::class);
    }

}
