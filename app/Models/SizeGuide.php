<?php

namespace App\Models;

// use App\Traits\TranslateFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Spatie\Translatable\HasTranslations;

class SizeGuide extends Model
{
    // use HasTranslations, TranslateFields ;
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'values',
    ];

}
