<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Employee;
use App\Models\UserVerification;
use App\Notifications\LoginNotification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Rest\Client;
use App\Services\VonageService;
use App\Services\syriatelOTPService;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\UserAuth\ForgetPasswordRequest;
use App\Http\Requests\UserAuth\ResetPasswordRequest;
use App\Http\Requests\UserAuth\RegisterUserRequest;
use App\Http\Requests\UserAuth\RegisterResendCodeRequest;
use App\Http\Requests\UserAuth\LoginUserRequest;
use App\Http\Requests\UserAuth\VerifyUserRequest;
use App\Traits\FirebaseNotificationTrait;




//use App\Models\PersonalAccessToken;


class RegisterUserController extends Controller
{
	use FirebaseNotificationTrait;

    //protected $syriatel_otp;
	private $syriatel_otp_username;
	private $syriatel_otp_password;
	private $syriatel_otp_templatecode;

    public function __construct() {
		$this->syriatel_otp_username = config('app.syriatel_otp_username');
        $this->syriatel_otp_password = config('app.syriatel_otp_password');
        $this->syriatel_otp_templatecode = config('app.syriatel_otp_templatecode');
    }
    /*    In Dubai Project

protected $vonageService;

    public function __construct(
        VonageService $vonageService
        )
    {
        $this->vonageService = $vonageService;
    } */


    //
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    public function register(Request $request)
    {
        try {
		//	$request->validated();
            $phone = $request->phone;
			
			
			$validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'phone' => ['required', 'regex:/^09\d{8}$/'],
        'email' => 'nullable|email|string|max:30',
        'password' => 'required|string|min:8|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
			
			
			
			

			
			
			// check if user has account and not verified his number
			
			
			$user_not_verified = User::where('phone',$phone)->where('isVerified',false)->first();
			
			if($user_not_verified){
				$user_id = $user_not_verified->id;
			$verify_code = (string) random_int(1000, 9999);
            //$verify_code =1234;

           UserVerification::where('user_id', $user_id)->delete();
            UserVerification::create([
                'user_id' => $user_id,
                'verify_code' => $verify_code,
				'expired_at' => now()->addMinutes(15)
            ]);
			
            ob_start();
            $ch = curl_init();
            curl_setopt(
                $ch,
                CURLOPT_URL,
                "https://bms.syriatel.sy/API/SendTemplateSMS.aspx?user_name=" .$this->syriatel_otp_username ."&password=" .$this->syriatel_otp_password ."&template_code=" .$this->syriatel_otp_templatecode . "&param_list=" .$verify_code ."&sender=XO&to=" .$phone
				
            );
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            if (curl_exec($ch) === false) {
                echo 'Curl error: ' . curl_error($ch);
            }
            curl_close($ch);
            ob_end_clean();
          
       
            /*  In Dubai Project
             $response = [
                 'user' => $user,
                 "request_id"=>$request_id,
                 'message' => 'User created successfully'
             ];
*/

			return response()->success(['message' => 'You have not verified your phone number yet we will sent OTP','user_id' => $user_not_verified->id],200);
			
			}
			
			
			else {
			
			 $validator->sometimes('phone', 'unique:users,phone', function ($input) {
        return $input->has('phone');
    });

    // Manually check if the new rule passes
   // $validator->validate();

    if ($validator->fails()) {
        return response()->error(['message'=>'The phone has already been taken'],400);
    }
			
			}
			
			$validated = $validator->validated();
			
			$email = $validated['email'];
			if($email){
				$existingUser = User::where('email', $email)->first();
				if ($existingUser) {
					return response()->error([
							'message' => 'The email has already been taken.',
						], 400);
				}
			}
			
            /*Here We must implement the Send OTP function from Syriatel and MTN for verification process */
            /* In Dubai Project
             $request_id=$this->vonageService->startVerification($phone); */
            $user = User::create([
                'first_name' =>$validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
              'email' => $validated['email']?? null,
				'password' => Hash::make($validated['password']),
            ]);
			 ///$user = User::create([
             //   'first_name' => $request->first_name,
             //   'last_name' => $request->last_name,
             //   'phone' => $request->phone,
             //   'email' => $request->email,
			 //	'password' => Hash::make($request->password),
             //]);

            $verify_code = (string) random_int(1000, 9999);

            UserVerification::create([
                'user_id' => $user->id,
                'verify_code' => $verify_code,
				'expired_at' => now()->addMinutes(15)
				//'expired_at' => now()->addSeconds(10)
            ]);

            ob_start();
            $ch = curl_init();
            curl_setopt(
                $ch,
                CURLOPT_URL,
                "https://bms.syriatel.sy/API/SendTemplateSMS.aspx?user_name=" .$this->syriatel_otp_username ."&password=" .$this->syriatel_otp_password ."&template_code=" .$this->syriatel_otp_templatecode . "&param_list=" .$verify_code ."&sender=XO&to=" .$phone
				
            );
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            
			if (curl_exec($ch) === false) {
                echo 'Curl error: ' . curl_error($ch);
            }
			
            curl_close($ch);
            ob_end_clean();

  
            /*  In Dubai Project
             $response = [
                 'user' => $user,
                 "request_id"=>$request_id,
                 'message' => 'User created successfully'
             ];
*/
			
			
			$respones = [
				
				 'user' => $user,
                'message' =>  'User created successfully' // trans('register_user.user_create',[],$request->header('Content-Language'))

			];
            return response()->success(

$respones ,

                Response::HTTP_CREATED
            );
        } catch (\Exception $ex) {
            return response()->error(
                ["error" => $ex->getMessage(), "message" => ""],
                400
            );
        }
    }

        public function resendCode(RegisterResendCodeRequest $request)
    {
        
        try {
            $phone = $request->validated('phone');
			$user = User::where('phone', $phone)->first();
			if(!$user){
				return response()->error(['message'=>'User not found'],400);
			}
			$user_id = $user->id;
            //$user_id = $request->validated('user_id');
            /*Here We must implement the Send OTP function from Syriatel and MTN for verification process */
            /* In Dubai Project
             $request_id=$this->vonageService->startVerification($phone); */
            $verify_code = (string) random_int(1000, 9999);
            //$verify_code =1234;

           UserVerification::where('user_id', $user_id)->delete();
            UserVerification::create([
                'user_id' => $user_id,
                'verify_code' => $verify_code,
				'expired_at' => now()->addMinutes(15)
            ]);
			
            ob_start();
            $ch = curl_init();
            curl_setopt(
                $ch,
                CURLOPT_URL,
                "https://bms.syriatel.sy/API/SendTemplateSMS.aspx?user_name=" .$this->syriatel_otp_username ."&password=" .$this->syriatel_otp_password ."&template_code=" .$this->syriatel_otp_templatecode . "&param_list=" .$verify_code ."&sender=XO&to=" .$phone
				
            );
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            if (curl_exec($ch) === false) {
                echo 'Curl error: ' . curl_error($ch);
            }
            curl_close($ch);
            ob_end_clean();
          
       
            /*  In Dubai Project
             $response = [
                 'user' => $user,
                 "request_id"=>$request_id,
                 'message' => 'User created successfully'
             ];
*/
             			return response()->success(['message' => trans('register_user.otp_create',[],$request->header('Content-Language')) ],201);
        } catch (\Exception $ex) {
            return response()->error(
                ["error" => $ex->getMessage(), "message" => ""],
                400
            );
        }
    }
	
    public function resetPassword(ResetPasswordRequest $request)
    {
        // Find the user by user_id and phone
        $user = User::where('phone', $request->phone)
            ->firstOrFail();

        // Hash the new password
        $password = Hash::make($request->password);

        // Update the user's password
        $user->forceFill([
            'password' => $password,
            'remember_token' => Str::random(60),

        ])->save();

        // Dispatch the PasswordReset event
        event(new PasswordReset($user));

        // Return a successful response
  return response()->success(['message' => trans('register_user.password_change',[],$request->header('Content-Language')) ],203);
    }


    public function refreshToken(Request $request)
    {
        // $refreshToken = $request->input('refresh_token');
        $user = null;
        if (auth()->guard('api-employees')->check()) {
            // The user is authenticated with the 'web' guard
            $user = auth()->guard('api-employees')->user();
            // Do something with the authenticated user
        } else if (auth()->guard('sanctum')->check()) {
            $user = auth()->guard('sanctum')->user();
        }


        //  $token = $user->personal_access_token;
        // Find the personal access token by the refresh token
        // $user->createToken('authToken', ['user'])->plainTextToken;
        /* if (!$token) {
             return response()->json(['error' => 'Invalid refresh token'], 401);
         }
     */
        // Generate a new access token
        $token = $user->createToken('authToken', ['user'])->plainTextToken;

        // Optionally, update the refresh token
        //  $user->token = $newAccessToken ;
        // $user->save();

        return response()->json([
            //  'access_token' => $newAccessToken,

            'user' => $user,
            'refresh_token' => $token,
        ]);
    }

    public function verify(VerifyUserRequest $request)
    {
        $phone = $request->phone;
        $code = $request->verification_code;
        $user = User::where('phone', $phone)->firstOrFail();

        if ($user->isVerified == 1) {
return response()->error(['message' => trans('register_user.user_is_verified',[],$request->header('Content-Language')) ],400);
        }
        $user_verify = UserVerification::where('user_id', $user->id)->latest()->firstOrFail();


        $user_verify_code = $user_verify->verify_code;
        /*  In Dubai Project
        $verify=$this->vonageService->checkVerification($request_id,$code);
        */
        try {
			
			 
            if ($user_verify_code != $code) {
           return response()->error(['message' => trans('register_user.invailed_otp',[],$request->header('Content-Language') ?? 'en') ],400);
            }
			

	else if (now()->greaterThanOrEqualTo(Carbon::parse($user_verify->expired_at))) {
return response()->error(['message' => trans('register_user.otp_expired',[],$request->header('Content-Language') ?? 'en') ],400);
}

            // Here we must check that the verification process is right 
            // and if the OTP is correct we set the $verify to true and update the user
        
            else if ($user_verify_code == $code) {
                $user = tap(User::where('phone', $phone)->firstOrFail())->update(['isVerified' => 1]);
                // $user=User::where('phone', $phone)->first();
                // $user->update(['isVerified' => true]);
                $token = $user->createToken('authToken', ['user'])->plainTextToken;
                $response = [
                    'user' => $user,
                    'token' => 'u' . '_' . $token,
                    'message' => 'User has been verified'
                ];
                $user_verification = UserVerification::where('user_id', $user->id)->delete();
				
				if (!$user_verification){
				return response()->error('Something went wrong',400);
				
				}
				
				$employee = Employee::whereHas('account', function ($query) {
						$query->whereHas('roles', function ($query) {
							$query->where('name','main_admin');	
						});
					})->first();
					
					if($employee){				
						$title = ["ar"=>" :تم إنشاء حساب مستخدم جديد باسم". $user->fullName,
						"en"=>"A new user account was created with name: ". $user->fullName];

						$body = ["ar"=>" :تم إنشاء حساب مستخدم جديد باسم". $user->fullName,
						"en"=>"A new user account was created with name: ". $user->fullName];

						$fcm_tokens = $employee->fcm_tokens()->pluck('fcm_token')->toArray();
						
						foreach($fcm_tokens as $fcm){
							$fcm_token = FcmToken::where([['fcm_token', $fcm],['employee_id',$employee->id]])->first();
							if($fcm_token->lang == 'en'){
								$this->send_notification($fcm, 
														 "A new user account was created with name: ". $user->fullName,
														 "A new user account was created with name: ". $user->fullName, 
														 'dashboard_customers', 
														 'flutter_app'); // key source	
							}else{
								$this->send_notification($fcm, 
														 " :تم إنشاء حساب مستخدم جديد باسم". $user->fullName,
														 " :تم إنشاء حساب مستخدم جديد باسم". $user->fullName,
														 'dashboard_customers', 
														 'flutter_app'); // key source
							}	
						}


						$employee->notifications()->create([
							'employee_id'=>$employee->id,
							'type'=> "dashboard_customers", // 1 is to redirect to the orders page
							'title'=>$title,
							'body'=>$body
						]);	
					}

                return response($response, 200);
            }
        } catch (\Exception $ex) {
            return response()->json([ "error" => $ex->getMessage(), "message" => "Something went wrong"], 400);
        }

      return response()->error(['message' => trans('register_user.invailed_otp',[],$request->header('Content-Language') ?? 'en') ],400);
    }
	
	public function getUserByToken(){
	
	   $user=  auth('sanctum')->check();
		
		return ['user' => $user] ;
	
	
	}
	
	
    public function verifyForPassword(VerifyUserRequest $request)
    {

        try {


       

            //  $phone = $request->phone;
            // $request_id=$request->request_id;
            // $code = $request->verification_code;
            // $user = auth('sanctum')->user();
            // $user_id  = $user->id;
            $user = User::where('phone', $request->phone)->firstOrFail();

            /*  if ($user->isVerified == 1) {
                  return response()->error(['message' => 'User is already verified'], 400);
              }

              */
            //$user_id = $user->id;
            $user_verify = UserVerification::where('user_id', $user->id)->latest()->firstOrFail();
			$code = $user_verify->verify_code;

            // $user_verify_code = $user_verify->verify_code;
            /*  In Dubai Project
            $verify=$this->vonageService->checkVerification($request_id,$code);
            */


            // Here we must check that the verification process is right 
            // and if the OTP is correct we set the $verify to true and update the user
            /* $verify = 0;
             if ($user_verify_code == $code) {
                 $verify = 1;
             }
             if ($verify == 1) {
                 $user = tap(User::where('phone', $phone)->first())->update(['isVerified' => 1]);
                 // $user=User::where('phone', $phone)->first();
                 // $user->update(['isVerified' => true]);
                 $token = $user->createToken('authToken', ['user'])->plainTextToken;
                 $response = [
                     'user' => $user,
                     'token' => 'u' . '_' . $token,
                     'message' => 'Phone number verified'
                 ];
                 UserVerification::where('user_id', $user->id)->delete();
     */
		if (now() > $user_verify->expired_at) {
		   return response()->error(['message' => trans('register_user.otp_expired',[],$request->header('Content-Language') ?? 'en') ],400);
		}


            if ($code == $request->verification_code) {

				$user_verifaction = UserVerification::where('user_id', $user->id)->delete();
				
				if(!$user_verifaction){
				
				return response()->error('Something went wrong!',400);
				}
				
                      return response()->success(['message' => trans('register_user.otp_confirmed',[],$request->header('Content-Language') ?? 'en') ],201);
            } else {
                              return response()->error(['message' => trans('register_user.incorrect_otp',[],$request->header('Content-Language') ?? 'en') ],400);

            }

        } catch (\Exception $ex) {
            return response()->error("The token is not valid or expired", 400);
        }

        return response()->error('Invalid verification code entered!', 400);


    }
        public function login(LoginUserRequest $request)
    {
        $phone = $request->validated('phone');
        $password = $request->validated('password');
        $user = User::where('phone', $phone)->firstOrFail();
		if ($user->is_deleted == 1){
		                      return response()->error(['message' => trans('register_user.user_phone_not_exist',[],$request->header('Content-Language') ?? 'en') ],400);
		}
        // return $user ;
        if ($user != null) {
			$user_password = $user->password;
			if (!Hash::check($password, $user_password)) {
					                      return response()->error( trans('register_user.wrong_password',[],$request->header('Content-Language') ?? 'en') ,400);

			}
           
        $verify_code = 0;
			
           
            if ($user->isVerified == 0) {
				
				
				
				$verify_code = (string) random_int(1000, 9999);

            UserVerification::create([
                'user_id' => $user->id,
                'verify_code' => $verify_code,
				'expired_at' => now()->addMinutes(15)
				//'expired_at' => now()->addSeconds(10)
            ]);
				
				
				  ob_start();
            $ch = curl_init();
            curl_setopt(
                $ch,
                CURLOPT_URL,
                "https://bms.syriatel.sy/API/SendTemplateSMS.aspx?user_name=" .$this->syriatel_otp_username ."&password=" .$this->syriatel_otp_password ."&template_code=" .$this->syriatel_otp_templatecode . "&param_list=" .$verify_code ."&sender=XO&to=" .$phone
				
            );
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            if (curl_exec($ch) === false) {
                echo 'Curl error: ' . curl_error($ch);
            }
            curl_close($ch);
            ob_end_clean();
				
				
				
				
                return response()->json(
					[
						'message' => 'Please verify your number',
					 	'user_id' => $user->id
					], 403);
            }
			
            if ($user->banned == 1) {

                // $user = auth('sanctum')->user();
                // $user=User::find($user->id);
                // return $user ;
                $banHistory = $user->histories()->latest()->first();

                if ($banHistory && Carbon::now()->lessThan($banHistory->end_date)) {
                    $start_date = Carbon::parse($banHistory->start_date);
                    $end_date = Carbon::parse($banHistory->end_date);

                    $startDateTime = new \DateTime($start_date->format('Y-m-d H:i:s'));
                    $endDateTime = new \DateTime($end_date->format('Y-m-d H:i:s'));

                    $interval = $startDateTime->diff($endDateTime);
                    $banned_days = $interval->days;

                    auth('sanctum')->user()->currentAccessToken()->delete();

                    if ($banned_days > 14) {
                        $message = 'Your account has been suspended. Please contact the administrator.';
                    } else {
                        $message = 'Your account has been suspended for ' . $banned_days . ' ' . Str::plural('day', $banned_days) . '. Please contact the administrator.';
                    }

                    return response()->json(["message" => $message]);
                }
            } else {
			
				 
                $token = $user->createToken('authToken', ['user']);
				 $cookie = cookie('login_token', $token->accessToken, 120, '/', null, false, true);
				
				 $refresh_token = $user->createToken('authToken', ['user_rt']);
				
				if(isset($request->app) && !empty($request->app)){
				
					$token->accessToken->expires_at = null;
				}
				
				else {
					$token->accessToken->expires_at = now()->addMinutes(10000);

				}
				
				 $refresh_token->accessToken->expires_at = now()->addMinutes(7 * 24 * 60);
				
				$token->accessToken->save();
				$refresh_token->accessToken->save();
				
                $user_token = $token->plainTextToken;
				$user_refresh_token = $refresh_token->plainTextToken;
               // $refresh_token = $user->createToken('TokenName')->plainTextToken;
                $response = [
                    'user' => $user,
                    'token' => 'u' . '_' . $user_token,
                    'refresh_token' => 'u' . '_' . $user_refresh_token,

                    'message' =>   trans('register_user.user_logged_in',[],$request->header('Content-Language'))
                ];
                return response()->success(

                    $response,
                    Response::HTTP_OK
                )->cookie($cookie);
            }
        } else {
         return response()->error(['message' => trans('register_user.user_phone_not_exist',[],$request->header('Content-Language') ?? 'en') ],400);
        }
    }

	
	
	
	
	
	public function revokeToken(){
	$user_tokens = auth('sanctum')->user()->tokens;
	$user_token = $user_tokens->sortByDesc('created_at')->firstOrFail();
	if( now() > $user_token->expires_at){
		
		$user_token->valid = 0;
		$user_token->save();
		
	return response()->success(['message' => 'Please, Login'],200);
	
	}
		
		else {
		return response()->noContent();
		}
	}
    // method to update email for user and send a new verification to a new his email

    //method to logout user

    public function forgotPassword(ForgetPasswordRequest $request)
    {
		
			$user = User::where('phone', $request->phone)->first();
			if (!$user){
				return response()->error(['message'=>'User Not Found'],400);	
			}
			if($user->isVerified == 0){
				return response()->error(['message'=>'This account exists but not verified yet'],400);	
			}
		
		try{
		
			
			
			

			$verify_code = (string) random_int(1000, 9999);

			UserVerification::create([
				'user_id' => $user->id,
				'verify_code' => $verify_code,
				'expired_at' => now()->addMinutes(15)
				//'expired_at' => now()->addSeconds(10)
			]);

			//ob_start();
		 ob_start();
            $ch = curl_init();
            curl_setopt(
                $ch,
                CURLOPT_URL,
                "https://bms.syriatel.sy/API/SendTemplateSMS.aspx?user_name=" .$this->syriatel_otp_username ."&password=" .$this->syriatel_otp_password ."&template_code=" .$this->syriatel_otp_templatecode . "&param_list=" .$verify_code ."&sender=XO&to=" .$user->phone
				
            );
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            
			if (curl_exec($ch) === false) {
                echo 'Curl error: ' . curl_error($ch);
            }
			
            curl_close($ch);
            ob_end_clean();
			//ob_end_clean();

			/* $status = Password::sendResetLink(
					$request->only('email')
				);
			*/

		   return response()->success(['message' => trans('register_user.otp_sent',[],$request->header('Content-Language') ?? 'en') ],200);
			{
					return response()->error(

						 'An error occurred. Please try again later.',

					 400);
				}
        } catch (\Exception $e) {

            return response($e, 400);
        }
    }
    public function logout()
    {
        try {
          auth('sanctum')->user()->currentAccessToken()->delete();
            return response()->success(
                'Logged out',
                Response::HTTP_OK
            );
        } catch (\Error $ex) {
            return response()->error(
                ["error" => $ex->getMessage(), "message" => "The token is not valid or expired"],
                Response::HTTP_UNAUTHORIZED
            );
        } catch (\Exception $ex) {
            return response()->error(
                ["error" => $ex->getMessage(), "message" => "The token is not valid or expired"],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }
}
