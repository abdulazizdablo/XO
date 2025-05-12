<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Requests\Users\ChangePasswordRequest;
use App\Http\Requests\Users\ChangePhoneRequest;
use App\Http\Requests\Users\ChangeEmailRequest;
use App\Http\Requests\Users\ChangeNameRequest;
use App\Http\Requests\Users\VerifyUpdatePhoneRequest;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FcmToken;
use App\Models\Notification;
use App\Models\UserVerification;
use App\Traits\FirebaseNotificationTrait;

class UserController extends Controller
{
    use FirebaseNotificationTrait;

    public function __construct(
        protected UserService $userService
    ) {}
    
    // for development only
    // public function create_user_token()
    // {
    //     $id = request('id');
    //     $user = User::find($id);
    //     $token = $user->createToken('authToken', ['*'])->plainTextToken;
    //     return response()->success([
    //         'token' => $token
    //     ], Response::HTTP_OK);
    // }

    public function showOrder() //si
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
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

    public function deleteUserNotification(Request $request) //si
    {
        $notification_id = $request->notification_id;
        $notification = Notification::FindOrFail($notification_id);
        $notification->deleted_at = now();
        $notification->save();
        return response()->success([], 204);
    }

    public function updatename(ChangeNameRequest $request) //si
    {
        try {
      
            $user = $this->userService->updatename($request->validated('user'), auth('sanctum')->id());

            return response()->success(
                [
                    'message' => 'User Updated successfully'
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

    public function updateemail(ChangeEmailRequest $request) //si
    {
        try {

          
          $this->userService->updateemail($request->validated('email'),auth('sanctum')->id());

            return response()->success(
                [
                    'message' => 'User Updated successfully'
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
    public function updatepassword(ChangePasswordRequest $request) //si
    {
        try {
    
            

         $this->userService->updatepassword($request->validated('user'), auth('sanctum')->id());

            return response()->success(
                [
                    'message' => 'User Updated successfully'
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

    public function updatephone(ChangePhoneRequest $request) //si
    {
        try {

 
            $user = $this->userService->updatephone($request->validated('phone'), auth('sanctum')->id());
			
			return response()->success(['message'=>'Phone Updated Successfully'],200);
			
            //return response()->success(['message' => trans('user.confirmation_code', [], $request->header('Content-Language')), 400]);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function verifyUpdatePhone(VerifyUpdatePhoneRequest $request) //si
    {
       

        $new_phone = $request->new_phone;
        $old_phone = $request->old_phone;
        $code = $request->verification_code;
        $user = User::where('phone', $old_phone)->firstOrFail();
        $user_id = $user->id;
        $user_verify = UserVerification::where('user_id', $user_id)->latest()->first();
        $user_verify_code = $user_verify->verify_code;

        try {
            $verify = 0;

            if ($user_verify_code == $code) {
                $verify = 1;
            }

            if ($verify == 1) {
                $user = tap(User::where('phone', $old_phone)->first())->update(['phone' => $new_phone]);
                UserVerification::where('user_id', $user_id)->delete();

                return response()->success(['message' => trans('user.phone_number_verified', [], $request->header('Content-Language'))], 200);
            }
        } catch (\Exception $ex) {
            return response()->json(["code" => 400, "error" => $ex->getMessage(), "message" => "The token is not valid or expired"], 400);
        }

        return response()->json(["code" => 400, 'message' => 'Invalid verification code entered! '], 400);
    }


    public function getUserNotifications() //si
    {
        try {
            $user = auth('sanctum')->user();
            
            if (!$user) {
                return response()->json('Unauthorized', 403);
            }

            $pageSize = request('pageSize');
            $notifications = Notification::where('user_id', $user->id)->latest()->paginate($pageSize);

            return response()->json(
                $notifications,
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
    public function forceDelete() //si
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->json('Unauthorized', 403);
            }
            return $this->userService->forceDelete($user->id);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }


    public function deactivate() //si
    {
        $user = auth('sanctum')->user();

        $user->isVerified  =  0;
        $user->save();

        return response()->success(['message' => 'User is deactivated'], 200);
    }

    public function getUserDataByToken() //si
    {
        $user = auth('sanctum')->user();

        return response()->success([
            'user' => $user
        ], Response::HTTP_OK);
    }

    public function getUserDataById() //si
    {
        $user = User::findOrFail(request('user_id'));
        return response()->success([
            'user' => $user
        ], Response::HTTP_OK);
    }

    public function updateUserLang(Request $request) //si
    { 
        $employee = auth('api-employees')->user();

        if (!$employee) {
            $user = auth('sanctum')->user();

            if (!$user) {
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

        if ($employee) {
            $fcm = FcmToken::where([['fcm_token', $request->fcm_token], ['employee_id', $employee->id]])->firstOrFail();
        } else{
            $fcm = FcmToken::where([['fcm_token', $request->fcm_token], ['user_id', $user->id]])->firstOrFail();
        }
        $fcm->update(['lang' => $request->lang ?? null]);
        return response()->json(['message' => 'User language has been updated successfully'], 200);
    }


    public function addFcmToken(Request $request) //si
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
                    'device_name' => 'test',
                    'lang' => $request->post('lang') ?? null,
                ]);
            }

            $old_tokens = FcmToken::where([['fcm_token', $request->fcm_token], ['user_id', '!=', $user->id]])->get();

            foreach ($old_tokens as $old_token) {
                $old_token->delete();
            }

            $fcm_tokens = $user->fcm_tokens()->latest()->take(5)->pluck('id');
            FcmToken::whereNull('employee_id')->where([['user_id', $user->id], ['fcm_token', $request->post('fcm_token')]])
                ->update(['lang' => $request->post('lang') ?? null]);
            FcmToken::whereNull('employee_id')->where('user_id', $user->id)->whereNotIn('id', $fcm_tokens)->delete();
            return response()->json(['message' => 'User token has been updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
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
}
