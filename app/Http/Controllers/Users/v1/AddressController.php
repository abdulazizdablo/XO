<?php

namespace App\Http\Controllers\Users\v1;

use App\Models\Address;
use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\City;

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
	public function index() //si
	{
		$user = auth('sanctum')->user();
		
		if (!$user) {
			return response()->error('Unauthorized', 403);
		}

		//$user_id = $user->id;
		$addresses = $this->addressService->getAddressByUserid($user->id);
		return response()->success(	$addresses, Response::HTTP_OK);
	}

	public function userAddresses()
	{ //si
		$user = auth('sanctum')->user();

		if (!$user) {
			return response()->success(['user' => null, 'user_address' => []], 200);
		} else {
			$user->load('addresses');
		}
		$cities = City::all('id', 'name');
		$user->addresses->each(function ($item) use ($cities) {
			$city_name = $cities->where('id', $item->city_id)->firstOrFail();
			$item->city = $city_name;
		});
		return response()->success(['user' => $user, 'user_address' => $user->addresses], 200);
	}

	/**
	 *
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreAddressRequest $request) //si
	{
		try {
			$user = auth('sanctum')->user();
			$address_data = $request->validated('address');

			if (isset($address_data['city'])) {
				$city = City::where('name->en', $address_data['city'])->orWhere('name->ar', $address_data['city'])->firstOrFail();
			}

			if (isset($address_data['city_id'])) {
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
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\Address  $address
	 * @return \Illuminate\Http\Response
	 */
	public function update(UpdateAddressRequest $request) //si
	{
		try {

			$address = $this->addressService->updateAddress( $request->validated()['address'], auth('sanctum')->user()->id);

			return response()->success(
				[
					'address' => $address,
					'message' => 'Address updated successfully'
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
	 * @param  \App\Models\Address  $address
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request) //si
	{
		try {
			$validatedData = $request->validate([
				'address_id' => ['required', 'integer', 'exists:addresses,id'],
			]);
			$address_id = $validatedData['address_id'];
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
