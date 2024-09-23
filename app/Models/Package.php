<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TranslateFields;


class Package extends Model
{
    use HasTranslations,TranslateFields;
    use HasFactory, SoftDeletes;

    protected $translatable = [ 'name' ];

    protected $fillable = [
        'name',
        'type',
        'valid',
        'photo_url',
        'thumbnail',
    ];

    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function scopeValid($query){
        return $query->where("valid", 1);
    }

}
