<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected static function booted()
    {

        static::updating(function ($model) {
            $model->offsetUnset('isLinked');
        
        });


        // Add an attribute to the model when retrieved from the database
        static::retrieved(function ($model) {

        

            $model->isLinked = Employee::where('account_id', $model->id)->first();
        });
    }

    /* protected $attributes = [
        'isLinked' => 'default_value',
    ];*/
	protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
	
    protected $fillable = ['email', 'password'];

    //protected $appends = ['isLinked'];

    //protected $appends = ['isLinked'];



   /* protected $attributes = [
        'isLinked' => 'default_value',
    ];*/



    //protected $appends = ['isLinked'];

    public function roles()
    {

        return $this->belongsToMany(Role::class);
    }

    public function assign_durations()
    {


        return $this->hasMany(AssignDuration::class);
    }

    /* public function getRoleAttribute(){



        return $this->roles->first()->name;
    }*/

    public function getisLinkedAttribute()
    {
        // Customize this logic based on your actual date attribute
        return Employee::where('account_id', $this->id)->first();
    }
}
