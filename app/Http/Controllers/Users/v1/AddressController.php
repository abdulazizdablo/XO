<?php

namespace App\Http\Controllers\Users\v1;

use App\Models\User;
use App\Models\Address;
use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressCollection;
use App\Http\Resources\AddressResource;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use App\Models\City;
use App\Models\Discount;
use App\Models\Group;
use App\Models\Product;


class AddressController extends Controller
{
   // protected $addressService;

    public function __construct(
      protected  AddressService $addressService
    ) {
      //  $this->addressService = $addressService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

       $user = auth('sanctum')->user();
       if(!$user){
           return response()->error('Unauthorized',403);
       }
      //  $user_id  = 19;
      $user_id = $user->id;
   
        $addresses = $this->addressService->getAddressByUserid($user_id);

   
        return response()->success(


        $addresses
//   AddressResource::collection($addresses)
,
            Response::HTTP_OK
        );

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

	
	
	public function userAddresses(Request $request){
	
		$user = auth('sanctum')->user();
		
		if(!$user){
		$user = null;
		
		}
		else {
		$user->load('addresses');
		
		}
		$cities = City::all('id','name');
	
		$user->addresses->each(function($item) use($cities){
		$city_name = $cities->where('id',$item->city_id)->firstOrFail();
		$item->city =$city_name;
		
		});
		
		return response()->success(['user' => $user,'user_address' => $user->addresses],200);
		
	
	
	
	}
	public function flash(Request $request){
		
		$expired_discounts = Discount::where('end_date','<', now())->get();

		foreach($expired_discounts as $expired_discount){
			Group::where('id', $expired_discount->group_id)->update(['valid' => 0]);
			$expired_discount->update([
										'valid'=>0
									]);
		}
		
		
		
		$flash_sale_products = Product::whereHas('discount',function($query){
		
		
		$query->where('end_date','<', now());
		
		})->get();
		
		
foreach($flash_sale_products as $flash_product){

$flash_product->discount->valid = 0;
	$flash_product->discount->save();

}
		
	
		
		
		
		
		
		$expired_discounts = Discount::where('end_date','<', now())->pluck('id');
	
		$discounts = Discount::where('valid' , 0)->get()->pluck('group_id');
		$products = Product::whereIn('group_id',$discounts)->get();

		foreach ($products as $product){
			$pvs = $product->product_variations;
			foreach($pvs as $pv){
				$pv->update(['group_id' => null]);	
			}
			$product->update([
								'group_id' => null,
							 	'discount_id' => null,
								'valid' => 0,
							 ]);
			
		}
	
	
		
	}
	

	
    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAddressRequest $request)
    {
        try {
              $user = auth('sanctum')->user();
              if(!$user){
           return response()->error('Unauthorized',403);
       }
            $user_id  = $user->id;
            $address_data = $request->address;
            if(isset($address_data['city'])){
                $city = City::where('name->en',$address_data['city'])->orWhere('name->ar',$address_data['city'])->first();
            }
            if(isset($address_data['city_id'])){
                $city = City::where('id', $address_data['city_id'])->firstOrFail();
            }
            $city_id = optional($address_data)['city_id'] ?? optional($city)->id;
            $address = $this->addressService->createAddress($address_data, $user, $city_id, $city);

            return response()->success(
                [
                    'message' => 'Address Is Created',
                    'data' => $address
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $address_id = request('address_id');
            $address = $this->addressService->getAddress($address_id);

            return response()->success(
                $address,
                Response::HTTP_FOUND
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
              $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function edit(Address $address)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAddressRequest $request)
    {
        try {

            $user = auth('sanctum')->user();
            $user_id  = $user->id;

            $validated_data = $request->validated()['address'];

            $address = $this->addressService->updateAddress($validated_data, $user_id);

            return response()->success(
                ['address' =>$address,
                    'message' => 'Address updated successfully'
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $address_id = request('address_id');
            $addressService = $this->addressService->delete($address_id);

            return response()->success(
                [
                    'message' => 'Address deleted successfully'
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
}
