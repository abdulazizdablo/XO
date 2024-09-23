<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LastViewed extends Model
{
	protected $table = 'last_viewed';
	
    use HasFactory;
}
