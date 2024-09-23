<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Account;
use App\Models\FcmToken;
use App\Enums\Roles;


class Employee extends Authenticatable
{
    // use HasTranslations;
    use HasFactory, SoftDeletes;
    use HasApiTokens;
    use HasRoles;

    public $guard_name = 'api-employees';

    protected $fillable = [
        'inventory_id',
        'account_id',
        'shift_id',
        'account_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
    ];
    
	    protected $casts = [
        	'created_at' => 'datetime:Y-m-d H:i:s',
        	'updated_at' => 'datetime:Y-m-d H:i:s'
    ];
	
    protected $hidden = ['password'];
    
    public function hasRole(int $role_id)
    {
        return $this->account->roles->first()->id == $role_id;
    }
	    
    public function has_role($role_name)
    {
        return $this->account->roles->first()->name == $role_name;
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    



     public function shift(){
        return $this->belongsTo(Shift::class);
    }


    public function return_orders()
    {
        return $this->hasMany(ReturnOrder::class);
    }

    public function account()
    {

        return $this->belongsTo(Account::class);
    }

	
    public function fcm_tokens()
    {
        return $this->hasMany(FcmToken::class);
    }

 	public function notifications()
    {
        return $this->hasMany(Notification::class);
    }


    /*public function roles(){


        return $this->hasManyThrough(Role::class,);
    }*/

    public function assign_durations()
    {


        return $this->hasMany(AssignDuration::class);
    }


    public function unassignAccount($account)
    {
		$assign_duration =  $this->assign_durations()->where('account_id', $account->id)->latest()->first();
		$assign_duration->assign_to = now()/*->format('Y-m-d H:i:s')*/;
	
        $assign_duration->save();
		$this->account_id = null;
        $this->save();
	

    }

    public function inventory(){
        return $this->belongsTo(Inventory::class);
    }
	
	
	public  static function getEmployeeByRole(int $role_id)
{
    return Employee::whereHas('account', function ($query) use($role_id) {
        $query->whereHas('roles', function ($query) use($role_id) {
            $query->where('roles.id', $role_id);
        });
    })->firstOrFail();
}

    //public function fcm_token(){
        //return $this->belongsTo(FcmToken::class);
        
    //}
}
