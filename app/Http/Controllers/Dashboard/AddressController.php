<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\AddressService;
use Symfony\Component\HttpFoundation\Response;

class AddressController extends Controller
{

    public function __construct(
        protected AddressService $addressService
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $addresss = $this->addressService->getAllAddresss();

        return response()->success([
            'addresss' => $addresss
        ], Response::HTTP_OK);
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
            $address = $this->addressService->delete($address_id);

            return response()->success(
                [
                    'message' => 'Address deleted successfully',
                    'data' => $address
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
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $address_id = request('address_id');
            $address = $this->addressService->forceDelete($address_id);

            return response()->success(
                [
                    'message' => 'Address deleted successfully',
                    'data' => $address
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
}
