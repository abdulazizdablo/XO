<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Models\FcmToken;
use App\Models\Notification;
use App\Notifications\BanUserNotification;
use App\Models\Setting;
use App\Models\UserVerification;
use Carbon\Carbon;
use App\Traits\FirebaseNotificationTrait;
use Illuminate\Support\Facades\Password;




class UserController extends Controller
{
    use FirebaseNotificationTrait;

    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = $this->userService->getAllUsers();

        return response()->success([
            'users' => $users
        ], Response::HTTP_OK);
    }
    public function create_user_token()
    {
        //just for developing create user token
        $id = request('id');
        $token = $this->userService->createToken($id);
        return response()->success([
            'token' => $token
        ], Response::HTTP_OK);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function export()
    {
        // return Excel::download(new UsersExport, 'users.xlsx');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $user = auth('sanctum')->user();
  
            $user = $this->userService->getUser( $user->id);

            return response()->success(
                [
                    'user' => $user
                ],
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function showOrder()
    {
        try {
            $user = auth('sanctum')->user();
			if(!$user){
                return response()->json('Unauthorized', 403);
			}
			$user_id = $user->id;
            $order_id = request('order_id');

            $order = $this->userService->getOrder($order_id, $user_id);

            return $order;
        } catch (InvalidArgumentException $e) {
            return response()->error(

                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function ban_histroy()
    {
        try {
            // $user_id = auth('sanctum')->user()->id;
            $user_id = request('user_id');

            $user = $this->userService->ban_histroy($user_id);

            return response()->success(
                [
                    'user' => $user
                ],
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(

                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function deleteUserNotification(Request $request)
    {

        $user = auth('sanctum')->user();

        $notification_id = $request->notification_id;

        $notification = Notification::FindOrFail($notification_id);

        $notification->deleted_at = now();

        $notification->save();


        return response()->success([], 204);
    }
    public function showUserOrders(Request $request)
    {
        try {
             $user = auth('sanctum')->user();
			
	

            // $user_id  =$user->id;
           // $user_id = 1;
            $filter_data = $request->only(['date_min', 'date_max', 'invoice_number', 'status']);
            $sort_data = $request->only(['sort']);
            // return $sort_data;
            $orders = $this->userService->getUserWebsiteOrders($user->id, $filter_data, $sort_data);

            return response()->success(
                [
                    $orders
                ],
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(

                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->json('Unauthorized', 403);
            }
            $user_id = $user->id;
            //   $user_id = 1 ;

            $validate = Validator::make(

                $request->all(),
                [
                    'user.first_name' => 'required|string|max:255',
                    'user.last_name' => 'required|string|max:255',
                    'user.email' => 'sometimes|email|unique:users',
                    'user.password' => 'sometimes|string|max:255',
                    'user.address' => 'sometimes|string|max:255',
                    'user.phone' => 'sometimes|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(

                    $validate->errors(),
                  422
                );
            }

            $validated_data = $validate->validated();
            $user_data = $validated_data['user'];

            $user = $this->userService->updateUser($user_data, $user_id);

            return response()->success(
                [
                    'message' => 'User Updated successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function updatename(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->json('Unauthorized', 403);
            }
            $user_id = $user->id;
            //   $user_id = 1 ;

            $validate = Validator::make(
                $request->all(),
                [
                    'user.first_name' => 'required|string|max:255',
                    'user.last_name' => 'required|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(

                    $validate->errors(),
                   422
                );
            }

            $validated_data = $validate->validated();
            $user_data = $validated_data['user'];

            $user = $this->userService->updatename($user_data, $user_id);

            return response()->success(
                [
                    'message' => 'User Updated successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function updateemail(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->json('Unauthorized', 403);
            }
            $user_id = $user->id;
            //   $user_id = 1 ;

            $validate = Validator::make(
                $request->all(),
                [
                    'email' => 'sometimes|email|unique:users',
                ]
            );

            if ($validate->fails()) {
                return response()->error(

                    $validate->errors(),
                    Response::HTTP_OK
                );
            }

            $validated_data = $validate->validated();
            $user_data = $validated_data['email'];

            $user = $this->userService->updateemail($user_data, $user_id);

            return response()->success(
                [
                    'message' => 'User Updated successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
    public function updatepassword(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->json('Unauthorized', 403);
            }
            $user_id = $user->id;
            //   $user_id = 1 ;

            $validate = Validator::make(
                $request->all(),
                [
                    'user.old_password' => 'required|string|max:255',
                    'user.new_password' => 'required|string|max:255',

                ]
            );

            if ($validate->fails()) {
                return response()->error(

                    $validate->errors(),
            422
                );
            }

            $validated_data = $validate->validated();
            $user_data = $validated_data['user'];

            $user = $this->userService->updatepassword($user_data, $user_id);

            return response()->success(
                [
                    'message' => 'User Updated successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }


    public function confirmCode(ActivationCodeRequest $request)
    {
        $activation_code = $request->user()->code;

        try {
            $user = User::where('activation_code', $activation_code)->first();

            if ($user) {
                $user->markEmailAsVerified();
                return response()->json([

                    'success' => true,
                    'message' => 'Account activated',


                    'headers' => [
                        'Accept' => 'application/json',
                    ]
                ]);
            } else {
                throw new \Exception('Invalid verification code');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ], 401);
        }
    }
    public function updatephone(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->json('Unauthorized', 403);
            }
            $user_id = $user->id;
            //   $user_id = 1 ;

            $validate = Validator::make(
                $request->all(),
                [
	            	'phone' => 'required|string|max:10',

                ]
            );

			
			
			
            if ($validate->fails()) {
                return response()->error(

                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

			
			
            $validated_data = $validate->validated();
            $user_data = $validated_data['phone'];
			
		
			
			//if($user->phone  != $user_data ){
			
			//return response()->error(['message' => 'Phone related to exisiting user'],400);
			
			//}
            $user = $this->userService->updatephone($user_data, $user_id);

          return response()->success(['message' => trans('user.confirmation_code',[],$request->header('Content-Language')) ,400]);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function verifyUpdatePhone(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'old_phone' => ['required'],
                'new_phone' => ['required'],
                // 'request_id' =>['required'],
                'verification_code' => ['required']
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $new_phone = $request->new_phone;
        $old_phone = $request->old_phone;
        // $request_id=$request->request_id;
        $code = $request->verification_code;
        // $user = auth('sanctum')->user();
        // $user_id  = $user->id;
        $user = User::where('phone', $old_phone)->firstOrFail();
        $user_id = $user->id;
        $user_verify = UserVerification::where('user_id', $user_id)->first();
        $user_verify_code = $user_verify->verify_code;
        /*  In Dubai Project
        $verify=$this->vonageService->checkVerification($request_id,$code);
        */
        try {

            // Here we must check that the verification process is right 
            // and if the OTP is correct we set the $verify to true and update the user
            $verify = 0;
            if ($user_verify_code == $code) {
                $verify = 1;
            }
            if ($verify == 1) {
                $user = tap(User::where('phone', $old_phone)->first())->update(['phone' => $new_phone]);
                UserVerification::where('user_id', $user->id)->delete();
                $response = [
                    'message' => 'Phone number verified'
                ];
                UserVerification::where('user_id', $user_id)->delete();

            return response()->success(['message' => trans('user.phone_number_verified',[],$request->header('Content-Language')) ,200]);
            }
        } catch (\Exception $ex) {
            return response()->json(["code" => 400, "error" => $ex->getMessage(), "message" => "The token is not valid or expired"], 200);
        }

        return response()->json(["code" => 400, 'message' => 'Invalid verification code entered! '], 200);
    }


    public function getUserNotifications()
    {

        //$this->send_notification('dTQA1v57TqiSzrplskibog:APA91bEVOtyX-ZjbaeabDsf_lZ9c1qpVr3tsBh0R3QB91K-h9Cp1KXKD_czbasVEB1qGWqxgoG68elUYYuJ4fLm5EntBN5olnSsFWdSv9r2HFTA770gnd18r7ifG9idnGayWuL9inVfY', 'توفر المنتج الخاص بك!', 'توفر الآن المقاس واللون اللذي أضفته لقائمة اللإشعارات', 'dress', 'flutter_app');


        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->json('Unauthorized', 403);
            }
            // $user_id  = $user->id;
            //   $user_id = 1 ;
			$pageSize = request('pageSize');
            $notifications = Notification::where('user_id', $user->id)->latest()->paginate($pageSize);

            return response()->json($notifications,
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $user = auth('sanctum')->user();
            // $user_id = $user->id;
            $userService = $this->userService->delete($user->id);

            return response()->success(
                [
                    'message' => 'User deleted successfully'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $user = auth('sanctum')->user();
			if(!$user){
				return response()->json('Unauthorized',403);	
			}
			$userService = $this->userService->forceDelete($user->id);

            return response()->success(
                [
                    'message' => 'User deleted successfully'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
	
	
	public function deactivate(){
	$user = auth('sanctum')->user();
		if(!$user){
		
		return response()->error('Unauthorized',401);
		
		}
	
	$user->isVerified  =  0;
		$user->save();
		
		
		return response()->success(['message' => 'User is deactivated'],200);
	}
    public function getUserDataByToken()
    {


        $user = auth('sanctum')->user();

        return response()->success([
            'user' => $user
        ], Response::HTTP_OK);
    }

    public function getUserDataById()
    {

        $user = User::findOrFail(request('user_id'));

        return response()->success([
            'user' => $user
        ], Response::HTTP_OK);
    }


    public function Ban_user()
    {

        $user = User::findOrFail(request('user_id'));
        // return $user ;

        $start_date = request('start_date');
        $end_date = request('end_date');
        $reason = request('reason');
		$type = request('type');

        $user = $this->userService->Ban_user($user, $start_date, $end_date, $reason, $type);
        $key = Setting::where('key', 'BanUserNotification')->first();
        // $value = json_decode($key->value);
        // $title = $value->title;
        // $body = $value->body;
        // Notification::send($user, new BanUserNotification(
        //     $title,
        //     $body
        // ));
        return response()->success([
            'message' => "User bannded successfully",
            'user' => $user
        ], Response::HTTP_OK);
    }
    public function UnBan_user()
    {

        $user = User::findOrFail(request('user_id'));
        $user = $this->userService->UnBan_user($user);
        return response()->success([
            'message' => "User Unbannded successfully",
            'user' => $user
        ], Response::HTTP_OK);
    }

    public function percentageDifference()
    {

        $users = $this->userService->percentageDifference();

        return response()->json([$users], 200);
    }
	
	public function updateUserLang(Request $request){
		$employee = auth('api-employees')->user();
        
		if (!$employee) {
			$user = auth('sanctum')->user();
			
			if(!$user){
				return response()->json(['message' => 'Not Authenticated'], 401);
			}
        }
		
        $validator = Validator::make(
            $request->all(),
            [
                'fcm_token' => 'required',
				'lang' => 'nullable|in:en,ar',
            ]
        );
		
		if ($validator->fails()) {
            return response()->json(['message' => 'The token field is required'], 400);
        }
		
		if($employee){
			$fcm = FcmToken::where([['fcm_token',$request->fcm_token],['employee_id', $employee->id]])->firstOrFail();
		}else{
			$fcm = FcmToken::where([['fcm_token',$request->fcm_token],['user_id', $user->id]])->firstOrFail();
		}
		$fcm->update(['lang'=>$request->lang??null]);
		return response()->json(['message' => 'User language has been updated successfully'], 200);

	}


    public function addFcmToken(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Not Authenticated'], 401);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'fcm_token' => 'required',
				'lang' => 'nullable|in:en,ar',
                //'device_name' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['message' => 'The token field is required'], 400);
        }
        try {
            $exists = $user->fcm_tokens()->where('fcm_token', '=', $request->post('fcm_token'))->exists();
            if (!$exists) {
                $user->fcm_tokens()->create([
                    'fcm_token' => $request->post('fcm_token'),
                    // 'device_name' => $request->post('device_name')
                    'device_name' => 'test',
					'lang' => $request->post('lang')??null,
                ]);
            }
			$old_tokens = FcmToken::where([['fcm_token',$request->fcm_token],['user_id','!=',$user->id]])->get();
			foreach($old_tokens as $old_token){
				$old_token->delete();	
			}
			
			$fcm_tokens = $user->fcm_tokens()->latest()->take(5)->pluck('id');
			FcmToken::whereNull('employee_id')->where([['user_id',$user->id],['fcm_token',$request->post('fcm_token')]])
				->update(['lang' => $request->post('lang')??null]);
			FcmToken::whereNull('employee_id')->where('user_id',$user->id)->whereNotIn('id',$fcm_tokens)->delete();
            // $token = FcmToken::where('user_id', $user->id)->Where('device_name', $request->device_name)->first();
            // if ($token != null) {
            //     $fcmToken = $token->update([
            //         'fcm_token' => $request->fcm_token,
            //     ]);
            // } else {

            //     return $fcmToken = FcmToken::create([
            //         'user_id' =>  $user->id,
            // 		'device_name' =>$request->device_name,
            //         'fcm_token' => $request->fcm_token,
            //     ]);
            // }

            return response()->json(['message' => 'User token has been updated successfully'], 200);
        } catch (\Exception $e) {
            //report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
