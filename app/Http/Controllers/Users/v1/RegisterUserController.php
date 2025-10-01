<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\UserVerification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Requests\UserAuth\ForgetPasswordRequest;
use App\Http\Requests\UserAuth\ResetPasswordRequest;
use App\Http\Requests\UserAuth\RegisterUserRequest;
use App\Http\Requests\UserAuth\RegisterResendCodeRequest;
use App\Http\Requests\UserAuth\LoginUserRequest;
use App\Http\Requests\UserAuth\VerifyUserRequest;
use App\Models\FcmToken;
use App\Traits\FirebaseNotificationTrait;
use App\Traits\SyriatelSendOTPTrait;
use Illuminate\Support\Facades\Log;

class RegisterUserController extends Controller
{
	use FirebaseNotificationTrait;
	use SyriatelSendOTPTrait;

	public function construct()
	{
		$this->initializeSyriatelSendOTP(); // to solve conflict because both traits have construct function
	}

	public function register(RegisterUserRequest $request) //si
	{
		try {
			$validated = $request->validated();
			$phone = $validated['phone'];
			$email = $validated['email'];
			$user_not_verified = User::where('phone', $phone)->where('isVerified', false)->first();

			if ($user_not_verified) {
				$user_id = $user_not_verified->id;
				//$verify_code = (string) random_int(1000, 9999);
				$verify_code = '0000';
				UserVerification::where('user_id', $user_id)->delete();
				UserVerification::create([
					'user_id' => $user_id,
					'verify_code' => $verify_code,
					'expired_at' => now()->addMinutes(15)
				]);
				//$this->send_otp($verify_code, $phone);
				return response()->success(['message' => 'You have not verified your phone number yet we will sent OTP', 'user_id' => $user_not_verified->id], 200);
			} else {
				$phone_exists = User::where('phone', $phone)->exists(); //we didn't include it in the form request validation because the user might register once and do not verify his account so the phone number will be saved in the database and this is why we first check if the phone is used put not verified and if not the we check here if it is unique
				if ($phone_exists) {
					return response()->error(['message' => 'The phone has already been taken'], 400);
				}
			}

			if ($email) {
				$existingUser = User::where('email', $email)->first();
				if ($existingUser) {
					return response()->error([
						'message' => 'The email has already been taken.',
					], 400);
				}
			}

			$user = User::create([
				'first_name' => $validated['first_name'],
				'last_name' => $validated['last_name'],
				'phone' => $validated['phone'],
				'email' => $validated['email'] ?? null,
				'password' => Hash::make($validated['password']),
			]);

			//$verify_code = (string) random_int(1000, 9999);
			$verify_code = '0000';

			UserVerification::create([
				'user_id' => $user->id,
				'verify_code' => $verify_code,
				'expired_at' => now()->addMinutes(15)
			]);
			//$this->send_otp($verify_code, $phone);
			$respones = [
				'user' => $user,
				'message' =>  'User created successfully'
			];
			return response()->success($respones, Response::HTTP_CREATED);
		} catch (\Exception $ex) {
			return response()->error(
				["error" => $ex->getMessage(), "message" => ""],
				400
			);
		}
	}

	public function resendCode(RegisterResendCodeRequest $request) //si
	{
		try {
			$phone = $request->validated('phone');
			$user = User::where('phone', $phone)->firstOrFail();


			//$verify_code = (string) random_int(1000, 9999);
			$verify_code = '0000';
			UserVerification::where('user_id', $user->id)->delete();
			UserVerification::create([
				'user_id' => $user->id,
				'verify_code' => $verify_code,
				'expired_at' => now()->addMinutes(15)
			]);
			//$this->send_otp($verify_code, $phone);
			return response()->success(['message' => trans('register_user.otp_create', [], $request->header('Content-Language'))], 201);
		} catch (\Exception $ex) {
			return response()->error(
				["error" => $ex->getMessage(), "message" => ""],
				400
			);
		}
	}

	public function resetPassword(ResetPasswordRequest $request) //si
	{
		$user = User::where('phone', $request->phone)->firstOrFail();
		$user_verify = UserVerification::where('user_id', $user->id)->latest()->firstOrFail();
		$code = $user_verify->verify_code;
		//dd(	$request->validated('verification_code'));
		if (now() > $user_verify->expired_at) {
			return response()->error(['message' => trans('register_user.otp_expired', [], $request->header('Content-Language') ?? 'en')], 400);
		}

		if ($code == $request->validated('verification_code')) {

			$user_verifaction = UserVerification::where('user_id', $user->id)->delete();

			if (!$user_verifaction) {
				return response()->error('Something went wrong!', 400);
			}
		}

		$password = Hash::make($request->password);
		$user->forceFill([
			'password' => $password,
			'remember_token' => Str::random(60),
		])->save();
		event(new PasswordReset($user));
		return response()->success(['message' => trans('register_user.password_change', [], $request->header('Content-Language'))], 203);
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



		$token = $user->createToken('authToken', ['user'])->plainTextToken;



		return response()->json([
			//  'access_token' => $newAccessToken,

			'user' => $user,
			'refresh_token' => $token,
		]);
	}

	public function verify(VerifyUserRequest $request) //si
	{
		$phone = $request->validated('phone');
		$code = $request->validated('verification_code');
		$user = User::where('phone', $phone)->firstOrFail();

		if ($user->isVerified == 1) {
			return response()->error(['message' => trans('register_user.user_is_verified', [], $request->header('Content-Language'))], 400);
		}

		$user_verify = UserVerification::where('user_id', $user->id)->latest()->firstOrFail();
		$user_verify_code = $user_verify->verify_code;
		try {
			if ($user_verify_code != $code) {
				return response()->error(['message' => trans('register_user.invailed_otp', [], $request->header('Content-Language') ?? 'en')], 400);
			} else if (now()->greaterThanOrEqualTo(Carbon::parse($user_verify->expired_at))) {
				return response()->error(['message' => trans('register_user.otp_expired', [], $request->header('Content-Language') ?? 'en')], 400);
			} else if ($user_verify_code == $code) {
				$user = tap(User::where('phone', $phone)->firstOrFail())->update(['isVerified' => 1]);
				$token = $user->createToken('authToken', ['user'])->plainTextToken;
				$response = [
					'user' => $user,
					'token' => 'u' . '_' . $token,
					'message' => 'User has been verified'
				];
				$user_verification = UserVerification::where('user_id', $user->id)->delete();

				if (!$user_verification) {
					return response()->error('Something went wrong', 400);
				}

				$employee = Employee::whereHas('account', function ($query) {
					$query->whereHas('roles', function ($query) {
						$query->where('name', 'main_admin');
					});
				})->first();

				if ($employee) {
					$title = [
						"ar" => " :تم إنشاء حساب مستخدم جديد باسم" . $user->fullName,
						"en" => "A new user account was created with name: " . $user->fullName
					];

					$body = [
						"ar" => " :تم إنشاء حساب مستخدم جديد باسم" . $user->fullName,
						"en" => "A new user account was created with name: " . $user->fullName
					];

					$fcm_tokens = $employee->fcm_tokens()->pluck('fcm_token')->toArray();

					foreach ($fcm_tokens as $fcm) {
						$fcm_token = FcmToken::where([['fcm_token', $fcm], ['employee_id', $employee->id]])->first();
						if ($fcm_token->lang == 'en') {
							$this->send_notification(
								$fcm,
								"A new user account was created with name: " . $user->fullName,
								"A new user account was created with name: " . $user->fullName,
								'dashboard_customers,' . $user->id,
								'dashboard'
							); // key source	
						} else {
							$this->send_notification(
								$fcm,
								" :تم إنشاء حساب مستخدم جديد باسم" . $user->fullName,
								" :تم إنشاء حساب مستخدم جديد باسم" . $user->fullName,
								'dashboard_customers,' . $user->id,
								'dashboard'
							); // key source
						}
					}

					$employee->notifications()->create([
						'employee_id' => $employee->id,
						'type' => "dashboard_customers", // 1 is to redirect to the orders page
						'title' => $title,
						'body' => $body
					]);
				}

				return response($response, 200);
			}
		} catch (\Exception $ex) {
			return response()->json(["error" => $ex->getMessage(), "message" => "Something went wrong"], 400);
		}

		return response()->error(['message' => trans('register_user.invailed_otp', [], $request->header('Content-Language') ?? 'en')], 400);
	}

	public function getUserByToken()
	{

		$user =  auth('sanctum')->check();

		return ['user' => $user];
	}


	public function verifyForPassword(VerifyUserRequest $request) //si
	{
		try {
			$user = User::where('phone', $request->phone)->firstOrFail();

			$user_verify = UserVerification::where('user_id', $user->id)->latest()->firstOrFail();
			$code = $user_verify->verify_code;
			if (now() > $user_verify->expired_at) {
				return response()->error(['message' => trans('register_user.otp_expired', [], $request->header('Content-Language') ?? 'en')], 400);
			}

			if ($code == $request->verification_code) {

				return response()->success(['message' => trans('register_user.otp_confirmed', [], $request->header('Content-Language') ?? 'en')], 201);
			} else {
				return response()->error(['message' => trans('register_user.incorrect_otp', [], $request->header('Content-Language') ?? 'en')], 400);
			}
		} catch (\Exception $ex) {
			return response()->error($ex->getMessage(), 400);
		}

		return response()->error('Invalid verification code entered!', 400);
	}

	public function login(LoginUserRequest $request) //si
	{
		$banHistory = null;
		$phone = $request->validated('phone');
		$password = $request->validated('password');
		$user = User::where('phone', $phone)->first();

		if (!$user) {
			return response()->error(['message' => 'User not found'], 400);
		}

		if ($user->is_deleted == 1) {
			return response()->error(['message' => trans('register_user.user_phone_not_exist', [], $request->header('Content-Language') ?? 'en')], 400);
		}

		if ($user != null) {
			$user_password = $user->password;
			if (!Hash::check($password, $user_password)) {
				return response()->error(['message' => trans('register_user.wrong_password', [], $request->header('Content-Language') ?? 'en')], 400);
			}

			$verify_code = 0;

			if ($user->isVerified == 0) {
				//$verify_code = (string) random_int(1000, 9999);
				$verify_code = '0000';
				UserVerification::create([
					'user_id' => $user->id,
					'verify_code' => $verify_code,
					'expired_at' => now()->addMinutes(15)
				]);
				//$this->send_otp($verify_code, $phone);
				return response()->json(
					[
						'message' => 'Please verify your number',
						'user_id' => $user->id,
						'status' => 403
					],
					403
				);
			}

			if ($user->banned == 1) {
				$banHistory = $user->histories()->latest()->first();
			}

			if ($banHistory && Carbon::now()->lessThan($banHistory->end_date)) {
				$start_date = Carbon::parse($banHistory->start_date);
				$end_date = Carbon::parse($banHistory->end_date);

				$startDateTime = new \DateTime($start_date->format('Y-m-d H:i:s'));
				$endDateTime = new \DateTime($end_date->format('Y-m-d H:i:s'));

				$interval = $startDateTime->diff($endDateTime);
				$banned_days = $interval->days;

				if (auth('sanctum')->user()) {
					auth('sanctum')->user()->currentAccessToken()->delete();
				}

				if ($banned_days > 14) {
					$message = 'Your account has been suspended. Please contact the administrator.';
				} else {
					$message = 'Your account has been suspended for ' . $banned_days . ' ' . Str::plural('day', $banned_days) . '. Please contact the administrator.';
				}
				return response()->error(['message' => $message], 400);
			} else {

				$user->update(['banned' => 0]);
				$token = $user->createToken('authToken', ['user']);
				$cookie = cookie('login_token', $token->accessToken, 120, '/', null, false, true);

				$refresh_token = $user->createToken('authToken', ['user_rt']);

				if (isset($request->app) && !empty($request->app)) {

					$token->accessToken->expires_at = null;
				} else {
					$token->accessToken->expires_at = now()->addMinutes(10000);
				}

				$refresh_token->accessToken->expires_at = now()->addMinutes(7 * 24 * 60);

				$token->accessToken->save();
				$refresh_token->accessToken->save();

				$user_token = $token->plainTextToken;
				$user_refresh_token = $refresh_token->plainTextToken;
				$response = [
					'user' => $user,
					'token' => 'u' . '_' . $user_token,
					'refresh_token' => 'u' . '_' . $user_refresh_token,

					'message' =>   trans('register_user.user_logged_in', [], $request->header('Content-Language'))
				];
				return response()->success(

					$response,
					Response::HTTP_OK
				)->cookie($cookie);
			}
		} else {
			return response()->error(['message' => trans('register_user.user_phone_not_exist', [], $request->header('Content-Language') ?? 'en')], 400);
		}
	}

	public function revokeToken()
	{
		$user_tokens = auth('sanctum')->user()->tokens;
		$user_token = $user_tokens->sortByDesc('created_at')->firstOrFail();
		if (now() > $user_token->expires_at) {

			$user_token->valid = 0;
			$user_token->save();

			return response()->success(['message' => 'Please, Login'], 200);
		} else {
			return response()->noContent();
		}
	}
	// method to update email for user and send a new verification to a new his email

	//method to logout user

	public function forgotPassword(ForgetPasswordRequest $request)
	{
		$user = User::where('phone', $request->phone)->first();
		if (!$user) {
			return response()->error(['message' => 'User Not Found'], 400);
		}
		if ($user->isVerified == 0) {
			return response()->error(['message' => 'This account exists but not verified yet'], 400);
		}
		try {
			//$verify_code = (string) random_int(1000, 9999);
			$verify_code = '0000';
			UserVerification::create([
				'user_id' => $user->id,
				'verify_code' => $verify_code,
				'expired_at' => now()->addMinutes(15)
			]);
			//$this->send_otp($verify_code, $request->phone);
			return response()->success(['message' => trans('register_user.otp_sent', [], $request->header('Content-Language') ?? 'en')], 200); {
				return response()->error(
					'An error occurred. Please try again later.',
					400
				);
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
