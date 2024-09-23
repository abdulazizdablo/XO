<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'color_id',
        'thumbnail',
        'path',
        'main_photo',
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function color(){
        return $this->belongsTo(Color::class);
    }

}
