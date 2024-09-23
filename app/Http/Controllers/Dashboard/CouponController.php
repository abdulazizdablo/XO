<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class CouponController extends Controller
{


    public function __construct(
        protected CouponService $couponService
    ) {

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $type = request('type');
        $search = request('search');
        $coupons = $this->couponService->getAllCoupons($type, $search);

        return response()->success([
            'coupons' => $coupons
        ], Response::HTTP_OK);
    }


    public function names()
    {
        $coupons = $this->couponService->getAllNames();

        return response()->success([
            'coupns' => $coupons
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
    }

    public function revealGiftCardPassword()
    {

        $code = request('code');
        try {
            $password = $this->couponService->revealGiftPassword($code);
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


    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $employee = auth('api-employees')->user();
        try {
            $validate = Validator::make(
    $request->only('name', 'code', 'max_redemption', 'percentage', 'expired_at'),
    [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:255|unique:coupons,code',
        'max_redemption' => 'required|numeric',
        'percentage' => 'required|numeric|lt:45',
        'expired_at' => '', // Make sure to add a proper validation rule for expired_at
    ]
);


            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $coupon = $this->couponService->createCoupon( $validate->validated());

            return response()->success(
                [
                    'message' => 'coupn Is Created',
                    'data' => $coupon
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $coupon_id = request('coupon_id');
            $coupon = $this->couponService->getCoupon($coupon_id);

            return response()->success(
                [
                    'coupon' => $coupon
                ],
                Response::HTTP_FOUND
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
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function edit(Coupon $coupon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $coupon_id = request('coupon_id');

            $validate = Validator::make(
                $request->all(),
                [
                    'name' => 'sometimes|string|max:255',
                    'password' => 'sometimes|string|max:255',
                    'valid' => 'sometimes|boolean',
                    'max_redemption' => 'sometimes |numeric',
                    'amount_off' => 'sometimes|string|max:255',
                    'percentage' => 'sometimes|numeric|lt:100',
                    'expired_at' => 'sometimes',
                ]
            );

            if ($validate->fails()) {
                return response()->error(

                    $validate->errors(),
                   422
                );
            }

            $coupon = $this->couponService->updateCoupon( $validate->validated(), $coupon_id);

            return response()->success(
                [
                    'message' => 'Coupon updated successfully',
                    'data' => $coupon
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $coupon_id = request('coupon_id');
            $coupon = $this->couponService->delete($coupon_id);

            return response()->success(
                [
                    'message' => 'Coupon deleted successfully',
                    'data' => $coupon
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $coupon_id = request('coupon_id');
            $coupon = $this->couponService->forceDelete($coupon_id);

            return response()->success(
                [
                    'message' => 'Coupon deleted successfully',
                    'data' => $coupon
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    public function cards()
    {

        try {
            $cards = $this->couponService->cards();
            return response()->success(
                $cards,
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
}
