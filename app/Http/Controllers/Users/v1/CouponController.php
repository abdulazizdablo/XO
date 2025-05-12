<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GiftRequest\UpdateGiftCardRequest;
use App\Models\Coupon;
use App\Models\AppSetting;
use App\Services\CouponService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Crypt;

class CouponController extends Controller
{


	public function __construct(
		protected CouponService $couponService,
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

		$per_page = 4;
		$page = request('page');
		$user = auth('sanctum')->user();

		if (!$user) {
			return response()->error('Unauthorized', 401);
		}
		$coupons = $this->couponService->getAllUserCoupons($user->id, $per_page, $page);

		return response()->success(
			$coupons,
			Response::HTTP_OK
		);
	}

	public function checkGiftCard(Request $request)
	{
		try {
		

			$coupons = $this->couponService->checkGiftCard($request->code, $request->password, auth('sanctum')->id());

			return response()->json(
				['success' => true,'data' => $coupons,	'message' => 'Gift Card is applied successfully'],
				Response::HTTP_OK
			);
		} catch (Exception $e) {
			return response()->error(['message'=>$e->getMessage()],400);
		}

	}


	public function activeGiftCard()
	{

		$coupon_id = request('coupon_id');
		try {
			$message = $this->couponService->activeGiftCard(auth('sanctum')->id(), $coupon_id);
			if($message =='Please charge the gift card first'){
				return response()->error($message,400);
			}
			return response()->success(
				$message,
				Response::HTTP_OK
			);
		} catch (InvalidArgumentException $e) {
			return response()->error(
				$e->getMessage()
				,
				Response::HTTP_NOT_FOUND
			);
		}
	}
	public function deactiveGiftCard()
	{
	
		$coupon_id = request('coupon_id');
		try {
			$message = $this->couponService->deactiveGiftCard(auth('sanctum')->id(), $coupon_id);
			return response()->success(
				$message,
				Response::HTTP_OK
			);
		} catch (InvalidArgumentException $e) {
			return response()->error(
				$e->getMessage()
				,
				Response::HTTP_NOT_FOUND
			);
		}
	}
	public function revealGiftCardPassword()
	{
		
		$coupon_id = request('coupon_id');
		try {
			$password = $this->couponService->revealGiftCardPassword(auth('sanctum')->id(), $coupon_id);
			return response()->success(
				$password,
				Response::HTTP_OK
			);
		} catch (InvalidArgumentException $e) {
			return response()->error(
				$e->getMessage()
				,
				Response::HTTP_NOT_FOUND
			);
		}
	}

	public function changePassword(Request $request)
	{
		try {
			$user = auth('sanctum')->user();
			$validatedData = $request->validate(
				[
					'coupon_id' => 'required|integer|exists:coupons,id',
					'old_password' => 'required|string|min:8',
					'new_password' => 'required|string|min:8',
				]);
			$coupon_id = $validatedData['coupon_id'];
			$old_password = $validatedData['old_password'];
			$new_password = $validatedData['new_password'];
			$coupons = $this->couponService->changePassword($user->id, $coupon_id, $old_password,  $new_password);

			return response()->success(
				$coupons,
				Response::HTTP_OK
			);
		} catch (Exception $e) {
			return response()->error(
				$e->getMessage()
				, Response::HTTP_NOT_FOUND);
		}


	}


	/**
     * Display the specified resource.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
	public function show(Request $request)
	{
		try {
			$validatedData = $request->validate([
				'coupon_id' => ['required', 'integer', 'exists:coupons,id'],
			]);
			$coupon_id = $validatedData['coupon_id'];
			$coupon = $this->couponService->getCoupon($coupon_id);

			return response()->success(
				$coupon,
				Response::HTTP_OK
			);
		} catch (InvalidArgumentException $e) {
			return response()->error(
				$e->getMessage(),
				Response::HTTP_NOT_FOUND
			);
		}
	}
	public function getCouponByCode()
	{
		try {
			$coupon_code = request('code');
			$coupon = $this->couponService->getCouponByCode($coupon_code);

			return response()->json(

				['success' => true,'data' =>$coupon,	'message' => 'Coupon is applied successfully']


				,
				Response::HTTP_OK
			);
		} catch (InvalidArgumentException $e) {
			return response()->error(
				$e->getMessage(),
				Response::HTTP_NOT_FOUND
			);
		}
	}


	public function getUserGiftCards(Request $request)
	{
		try {
			// type : solved, opened
			$user = auth('sanctum')->user();

			$filter_data = $request->only(['status', 'created', 'last', 'value']);

			$cards = $this->couponService->getAllUserGiftCards($user->id, $filter_data);

			return response()->success(
				$cards,
				Response::HTTP_OK
			);
		} catch (Exception $e) {
			return response()->error(
				$e->getMessage(),
				Response::HTTP_NOT_FOUND
			);
		}
	}


	public function rechargeGiftCard(UpdateGiftCardRequest $request)
	{
		try {
			$user = auth('sanctum')->user();

			// $user_id = 11;
			return $data = $this->couponService->rechargeGiftCard($user->id, $request->validated());

		} catch (Exception $e) {
			return response()->error(
				$e->getMessage(),
				Response::HTTP_BAD_REQUEST
			);
		}

	}

	public function storeGiftCard(Request $request)
	{
		try {
			$key = AppSetting::where('key', 'GiftCardDetails')->firstOrFail();
			$max = json_decode($key->value)->balance->max;
			$validate = Validator::make(
				$request->all(),
				[
					'name' => 'required|string|max:255',
					'password' => 'required|string|min:8',
					'amount_off' => 'required|numeric|lte:'.$max,
					'payment_method' => 'required|string|in:syriatel-cash,mtn-cash,ecash,payment_method_1,payment_method_2,payment_method_3'
				]
			);

			if ($validate->fails()) {
				return response()->error(

					$validate->errors()
					,
					422
				);
			}

			$validated_data = $validate->validated();
			$user = auth('sanctum')->user();
			if (!$user) {
				return response()->error(
					'Unauthorized',
					422
				);

			}

			$coupon = $this->couponService->storeGiftCard($validated_data, $user->id);

			return response()->success(
				[
					'message' => 'giftCard Is Created',
					'data' => $coupon
				],
				Response::HTTP_CREATED
			);
		} catch (\Throwable $th) {
			return response()->error(
				$th->getMessage(),
				Response::HTTP_BAD_REQUEST
			);
		}
	}
}
