<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id',
        'verify_code',
		'expired_at'
    ];
}
