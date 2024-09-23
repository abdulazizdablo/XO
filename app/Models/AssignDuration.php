<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class AssignDuration extends Model
{
    use HasFactory;

    protected $fillable = ['assign_from', 'assign_to', 'employee_id', 'account_id'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'assign_from' => 'datetime:Y-m-d H:i:s',
        'assign_to' => 'datetime:Y-m-d H:i:s'
    ];


    /*protected $dates = [
        'created_at',
        'updated_at',
        'assign_from',
        'assign_to'
    ];*/


    /*protected function assignFrom(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->format('Y-m-d H:i:s'),

        );
    }*/

   /* protected function assignTo(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::parse($value)->format('Y-m-d H:i:s'),

        );
    }*/


    // Accessor for created_at
    /*public function getCreatedAtAttribute($value)
    {
        return $value->format('Y-m-d H:i:s');
    }*/

    // Accessor for updated_at
    /*public function getUpdatedAtAttribute($value)
    {
        return $value->format('Y-m-d H:i:s');
    }
*/
    // Accessor for assign_from
  /*  public function assignFrom($value)
    {
        return $value->format('Y-m-d H:i:s');
    }
*/
    // Accessor for assign_to
  /*  public function assignTo($value)
    {
        return $value->format('Y-m-d H:i:s');
    }
    */
}

