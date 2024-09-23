<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasTranslations;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'from_location_id',
        'to_location_id',
        'deliver_date',
        'quantity',
    ];

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function transfer_items(){
        return $this->hasMany(TransferItem::class);
    }

}
