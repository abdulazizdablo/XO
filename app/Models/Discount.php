<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TranslateFields;


class Discount extends Model
{
    use HasTranslations, TranslateFields;
    use HasFactory, SoftDeletes;

    public $translatable = ['name'];

    protected $fillable = [
        'group_id',
        'name',
        'type',
		'valid',
        'percentage',
        'start_date',
        'end_date',
    ];
    
    protected $casts = [
        'start_date' => 'datetime:Y-m-d H:i:s',
        'end_date' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    // public function products()
    // {
    //     return $this->morphMany(Product::class, 'promotionable');
    // }


    public function scopeValid($query)
    {
        $query->where('valid', 1);
    }

    public function isValid()
    {
        return $this->valid == 1 ? true : false;
    }

    public function groups()
    {
        return $this->belongsTo(Group::class);
    }

}
