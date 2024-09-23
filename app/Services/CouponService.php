<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use App\Services\EcashPaymentService;
use App\Traits\TranslateFields;
use Exception;
use Illuminate\Support\Str;
use App\Services\SyriatelCashService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Counts;
use App\Services\MTNCashService;
use Carbon\Carbon;


class CouponService
{
    use TranslateFields;

    public function __construct(
        protected EcashPaymentService $ecashPaymentService,

    ) {
    }

    public function getAllCoupons($type, $search)
    {

        if ($type == 'coupon') {
            $coupons = Coupon::select('name', 'code', 'max_redemption', 'percentage', 'expired_at', 'created_at')->where('type', 'coupon')->valid();
            if ($search != null) {
                $coupons = $coupons->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('code', 'LIKE', '%' . $search . '%')
                        ->orWhere('percentage', 'LIKE', '%' . $search . '%')
                        ->orWhere('max_redemption', 'LIKE', '%' . $search . '%');
                })
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
            } else {
                $coupons = $coupons->orderBy('created_at', 'desc')->paginate(10);
            }
        } elseif ($type == 'gift') {

            $coupons = Coupon::select('name', 'code', 'amount_off', 'created_at')
                ->where('type', 'gift')
                ->valid();

            if ($search != null) {
                $coupons = $coupons->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('code', 'LIKE', '%' . $search . '%')
                        ->orWhere('amount_off', 'LIKE', '%' . $search . '%');
                })
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
            } else {

                $coupons = $coupons->orderBy('created_at', 'desc')->paginate(10);
            }
        } elseif ($type == 'end_coupon') {
            $coupons = Coupon::select('name', 'code', 'max_redemption', 'percentage', 'expired_at', 'created_at')->where('type', 'coupon')->whereDate('expired_at', '<', now())->valid();
            if ($search != null) {
                $coupons = $coupons->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('code', 'LIKE', '%' . $search . '%')
                        ->orWhere('percentage', 'LIKE', '%' . $search . '%')
                        ->orWhere('max_redemption', 'LIKE', '%' . $search . '%');
                })
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
            } else {
                $coupons = $coupons->orderBy('created_at', 'desc')->paginate(10);
            }
        }

        if (!$coupons) {
            throw new InvalidArgumentException('There Is No Coupons Available');
        }

        return $coupons;
    }
    public function getAllNames()
    {
        $coupons = Coupon::where('type','coupon')->select('id', 'code')->valid()
            ->get();
        if (!$coupons) {
            throw new InvalidArgumentException('There Is No Coupons Available');
        }

        return $coupons;

        $coupon_fields = [
            'type',
            'name',
            'code',
            'password',
            'valid',
            'max_redemption',
            'amount_off',
            'percentage',
            'expired_at',
        ];

        return $this->getPaginatedTranslatedFields($coupons, $coupon_fields);
    }

    public function getAllUserCoupons($user_id, $per_page, $page)
    {
        $coupons = Coupon::select(['id', 'name', 'code', 'type', 'amount_off', 'valid', 'status'])->where('user_id', $user_id)->where('valid',1)->paginate($per_page);
        return $coupons;

        if (!$coupons) {
            throw new InvalidArgumentException('There Is No Coupons Available');
        }

        return $coupons;

        $coupon_fields = [
            'type',
            'name',
            'code',
            'password',
            'valid',
            'max_redemption',
            'amount_off',
            'percentage',
            'expired_at',
        ];

        return $this->getPaginatedTranslatedFields($coupons, $coupon_fields, $per_page, $page);
    }

    public function getAllUserGiftCards($user_id/*, $per_page, $page*/)
    {
        $coupons = Coupon::where('user_id', $user_id)->where('type', 'gift')->latest()->paginate(8);
        return $coupons;

        if (!$coupons) {
            throw new InvalidArgumentException('There Is No Coupons Available');
        }

        return $coupons;

        $coupon_fields = [
            'type',
            'name',
            'code',
            'valid',
            'max_redemption',
            'amount_off',
            'percentage',
            'expired_at',
        ];

        return $this->getPaginatedTranslatedFields($coupons, $coupon_fields, $per_page, $page);
    }

    public function checkGiftCard($code, $password, $user_id)
    {

        $coupon = Coupon::where('code', $code)->valid()->first();
        if ($coupon != null) {
			
			
           // Assuming $coupon is retrieved from a database or similar
if ($coupon && Hash::check( $password,$coupon->password)) {
    return $coupon;
} else {
    // Ensure you're handling the exception appropriately in your application
    throw new Exception('Wrong Password');
}

        } else {
            throw new ModelNotFoundException('Gift Card not found');
        }
    }

    public function activeGiftCard($user_id, $coupon_id)
    {

        $coupon = Coupon::where([['id', $coupon_id], ['type', 'gift']])->firstOrFail();
		
	
        if ($coupon != null) {
			if($coupon->valid == 0){
				return 'Please charge the gift card first';
			}
            if ($coupon->user_id == $user_id) {
                $coupon->update([
                    //"valid" => true,
					"status" => 1,
                    "amount_off" => Crypt::encryptString($coupon->amount_off)
                ]);
                return $message = "GiftCard has been activated";
            }
        } 
    }
    public function deactiveGiftCard($user_id, $coupon_id)
    {

        $coupon = Coupon::where('id', $coupon_id)->where('valid',1)->first();
        if ($coupon != null) {
            if ($coupon->user_id == $user_id) {
                $coupon->update([
                    //"valid" => false,
					"status" => 0,
                    "amount_off" => Crypt::encryptString($coupon->amount_off)
                ]);
                return $message = "GiftCard has been deactived";
            }
        } elseif (!$coupon) {
            throw new InvalidArgumentException('Gift Card not found');
        }
    }
    public function revealGiftPassword($code)
    {
        $coupon = Coupon::where('code', $code)->where('type', 'gift')->valid()->first();

        if (!$coupon) {
            throw new ModelNotFoundException('Coupon not found');
        } else {

            $password = Crypt::decryptString($coupon->password);
            return $password;
        }
    }
    public function revealGiftCardPassword($user_id, $coupon_id)
    {
        $coupon = Coupon::where('id', $coupon_id)->valid()->firstOrFail();
        if ($coupon != null) {
            if ($coupon->user_id == $user_id) {
                $password = Crypt::decryptString($coupon->password);
                return $password;
            }
        } 
    }
    public function getCoupon($coupon_id)
    {
        $coupon = Coupon::valid()->findOrFail($coupon_id);
        $coupon->amount_off = Crypt::decryptString($coupon->amount_off);
  

        $coupon_fields = [
            'type',
            'name',
            'code',
            'password',
            'valid',
            'max_redemption',
            'amount_off',
            'percentage',
            'expired_at',

        ];
        return $coupon->getFields($coupon_fields);
    }
    public function rechargeGiftCard($user_id, $request)
    {
        $payment_method = $request['payment_method'];
        $password = $request['password'];
        $coupon = Coupon::where([['user_id', $user_id], ['type', 'gift']])->findOrFail($request['coupon_id']);
        // return $coupon_password = Crypt::decryptString($coupon->password);
        // return $amount_off = Crypt::decryptString($coupon->amount_off);
        $amount_off = $coupon->amount_off;
        $value = $request['value'];
        $new_amount = $value + $amount_off;

     //   $new_amount = Crypt::encryptString($new_amount);
		//$coupon->amount_off = $new_amount;
	//	$coupon->save();
        //if (!$coupon->valid) {
        //    throw new Exception('Coupon is not valid');
        //} else {

            if (!Hash::check($password, $coupon->password)) {
                // return response()->error('you have entered incorrect Password',400);
                throw new Exception('you have entered incorrect Password', 400);
            }

            if (Hash::check($password, $coupon->password)) {
                $transaction = Transaction::create([
					'transaction_uuid' => Str::random(5),
                    'user_id' => $user_id,
                    'gift_id' => $coupon->id,
                    'amount' => $value,
                    'status' => 'pending',
                    'operation_type' => 'recharge-gift-card'
                ]);



                if ($payment_method == 'ecash' || $payment_method == 'payment_method_3') {
                    $data = [
						"amount" => $value,
						"coupon" =>  $coupon->id,
						"ref" => $transaction->transaction_uuid,
						"lang" => "EN"
					];
					
					$full_url = $this->ecashPaymentService->chargeGiftCard($data);
                    return response()->success($full_url,200);
                } else if ($payment_method == 'syriatel-cash' || $payment_method == 'payment_method_1') {

return response()->success([
    'coupon' => $coupon,
    'message' => 'You will be redirected to Syriatel Cash Page'
], 200);

                    // $syriatel_response = $this->syriatelCashService->paymentRequest($data);

                } else if ($payment_method == 'mtn-cash' || $payment_method == 'payment_method_2') {


                  return response()->success([
    'coupon' => $coupon,
    'message' => 'You will be redirected to MTN Cash Page'
], 200);


                    /*  $data=[
				
                   
                    
                    "amount"=> $request->value,
             
                    "lang"=>"EN"
                ];

                $mtn_response= $this->mtnCashService->PaymentInitiateRequest($data);*/
                }
                /*if($payment_method == 'syriatel-cash'){
            $data=[
				
				"customerMSISDN" $request->customerMSISDN,
				
                "amount"=>100000,
                "ref"=>$transaction->id,
                "lang"=>"EN"
            ];
			
			$syriatel_response = $this->SyriatelCashService->paymentRequest($data)
				
				*/




                // 'amount_of' => $new_amount
                // $coupon->amount_off = Crypt::encryptString($new_amount);
                //$coupon->save();
                // $coupon->amount_off = Crypt::encryptString($value);
                // $coupon->save();  
            }
        //}

        return $coupon;
    }

    public function getCouponByCode($code)
    {
        $coupon = Coupon::where('code', $code)->valid()->firstOrFail();
     

        // $coupon_fields = [
        //     'type',
        //     'name',
        //     'code',
        //     'password',
        //     'valid',
        //     'max_redemption',
        //     'amount_off',
        //     'percentage',
        //     'expired_at',

        // ];
        return $coupon;
    }
    public function changePassword($user_id, $coupon_id, $old_password,  $new_password)
    {   
        $coupon = Coupon::select('id','password')->where([['id',$coupon_id],['user_id',$user_id]])->valid()->firstOrFail();

        if (!Hash::check($old_password, $coupon->password)) {
            throw new Exception('Wrong password');
           
        }
        $coupon->password = Hash::make($new_password);
       
        $coupon->save();

        return 'Your password changed';
    }
	
	
    public function cards()
    {

        $coupon_number = Coupon::where('type', 'coupon')->count();
        $gift_number = Coupon::where('type', 'gift')->count();
        $total_buy = Order::whereHas('coupon')->sum('total_price');
        $all = [
            'coupon_number' => $coupon_number,
            'gift_number' => $gift_number,
            'total_buy' => $total_buy,
        ];
        return $all;
    }


    public function storeGiftCard(array $data, $user_id)
    {
        $payment_method = $data['payment_method'];
		if((Coupon::where([['created_at', '>', Carbon::now()->startOfDay()],['user_id', $user_id]])->count()) >= 10){
			throw new Exception('you have reached max limit of gift cards for this day');
		}
        $coupon = Coupon::create([
            'user_id' => $user_id,
            'type' => 'gift',
            'code' => Str::random(8),
            'name' => $data['name'],
            'password' => Hash::make(request('password')),
            'valid' => 0,
			'status'=> 0,
            'amount_off' => Crypt::encryptString(0),
			'last_recharge' => now()->format('Y-m-d H:i:s'),
            //'amount_off' => Crypt::encryptString(request('amount_off')),
        ]);

        if (!$coupon) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }
     
        if ($payment_method == 'ecash') {
            $transaction = Transaction::create([
                'user_id' => $user_id,
                'transaction_uuid' => Str::random(5),
                'gift_id' => $coupon->id,
                'amount' => 0,
                'status' => 'pending',
                'operation_type' => 'create-gift-card'
            ]);
            $data = [
                "amount" => $data['amount_off'],
                "coupon" =>  $coupon->id,
                "ref" => $transaction->transaction_uuid,
                "lang" => "EN"
            ];
            return $full_url = $this->ecashPaymentService->chargeGiftCard($data);
        }
        $coupon->amount_off = Crypt::decryptString($coupon->amount_off);
        return $coupon;
    }


    public function createCoupon(array $data): Coupon
    {
        $coupon = Coupon::create([
            'name' => $data['name'],
            'type' => 'coupon',
            'code' => $data['code'],
            'valid' => 1,
			'status' =>1,
            'max_redemption' => $data['max_redemption'],
            // 'amount_off' => $data['amount_off'],
            'percentage' => $data['percentage'],
            'expired_at' => $data['expired_at'],
        ]);

        if (!$coupon) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $coupon;
    }

    public function updateCoupon(array $data, $coupon_id): Coupon
    {
        $coupon = Coupon::findOrFail($coupon_id);
       
        if (isset($data['password'])) {
            $data['password'] = Crypt::encryptString(request('password'));
        }

        $coupon->update($data);

        return $coupon;
    }

    public function show($coupon_id): Coupon
    {
        $coupon = Coupon::findOrFail($coupon_id);

        return $coupon;
    }

    public function delete(int $coupon_id): void
    {
        $coupon = Coupon::findOrFail($coupon_id);
		
        $coupon->delete();
    }

    public function forceDelete(int $coupon_id): void
    {
        $coupon = Coupon::findOrFail($coupon_id);
		
        $coupon->forceDelete();
    }
}
