<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class CheckUserNotVerfiedNumber implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
	
	
	   protected $user;
	
	
    public function __construct()//string $phone))
    {
     //  $this->user = User::where('phone',$value)->where('isVerified',false);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
       	$user_not_verified = User::where('phone',$value)->where('isVerified',false)->first();
		$this->user = $user_not_verified;
			
		return !$user_not_verified;
			
    }

    /**
     * Get the validation error message.
     *
     * @return string
	 
	 
	 
     */
	
	
	  public function status()
    {
        return 409;
    }
    public function message()
    {
        return 'Your phone number has not been verified yet __ ' . $this->user->id ;
    }
	
	
	
}
