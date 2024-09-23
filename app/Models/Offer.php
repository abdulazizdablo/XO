<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TranslateFields;
use Carbon\Carbon;

class Offer extends Model
{
    use HasTranslations,TranslateFields;
    // use HasFactory, SoftDeletes;

    public $translatable = ['name'];

    protected $appends = ['counter'];

    protected $fillable = [
        'group_id',
        'name',
        'type',
    ];

    protected $casts = [
      
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',

    ];

    // public function offers()
    // {
    //     return $this->morphMany(Product::class, 'promotionable');
    // }

    public function groups()
    {
        return $this->belongsTo(Group::class);
    }
    
    public function getCounterAttribute()
    {
        $expiredAt = Carbon::parse($this->expired_at);
        $currentDateTime = Carbon::now();

        $counter = $expiredAt->diff($currentDateTime);

        $readableCounter = [
            'hours' => $counter->format('%h'),
            'minutes' => $counter->format('%i'),
            'seconds' => $counter->format('%s')
        ];
        return $readableCounter;
    }

}
