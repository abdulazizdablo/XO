<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
		'branch_id',
        'first_name',
        'father_name',
        'last_name',
        'phone',
		'isKadmous',
        'city',
        'city_id',
        'neighborhood',
        'street',
        'another_details',
        'lat_long',
        'phone_number_two'
    ];

	
	protected $casts = [
	
	'isKadmous' => 'bool'
	];
	
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
