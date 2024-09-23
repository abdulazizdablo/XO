<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
	
	
	 public $translatable = [
        'name',
    ];

    protected $fillable = [
        'name',
        'city_id'
    ];

    public function city(){
        return $this->belongsTo(City::class);
    }
}
