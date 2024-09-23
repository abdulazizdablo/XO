<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class TransferController extends Controller
{


    public function __construct(
        protected TransferService $transferService
        )
    {
  
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transfers = $this->transferService->getAllTransfers();

        return response()->success([
            'Transfers' => $transfers
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

    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $from_location_id = request('from_location_id');
            $to_location_id = request('to_location_id');
            $validate = Validator::make($request->all(),
                [
                    'Transfer.name' => 'required|array',
                    'Transfer.name.*' => 'max:255',
                    'Transfer.address' => 'required|array',
                    'Transfer.address.*' => 'max:255',
                    'Transfer.city' => 'required|array',
                    'Transfer.city.*' => 'max:255',
                ],
                [
                    'Transfer.name.required' => 'this field is required',
                    'Transfer.name.max' => 'this field maximun length is 255',
                    'Transfer.address.required' => 'this field is required',
                    'Transfer.address.max' => 'this field maximun length is 255',
                    'Transfer.city.required' => 'this field is required',
                    'Transfer.city.max' => 'this field maximun length is 255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                         $validate->errors()

                    , Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();
            $transfer_data = $validated_data['transfer'];

            $transfer = $this->transferService->createTransfer($transfer_data, $from_location_id, $to_location_id);

            return response()->success(
                [
                    'message'=>'Transfer Is Created',
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_OK
            );
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $transfer_id = request('transfer_id');
            $transfer = $this->transferService->getTransfer($transfer_id);

            return response()->success([
                'transfer' => $transfer],
            Response::HTTP_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->error(
              $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\Response
     */
    public function edit(Transfer $transfer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $from_location_id = request('from_location_id');
            $to_location_id = request('to_location_id');
            $transfer_id = request('transfer_id');

            $validate = Validator::make($request->all(),
                [
                    'Transfer.name' => 'sometimes|array',
                    'Transfer.address' => 'sometimes|array',
                    'Transfer.city' => 'sometimes',
                ],
                [
                    'Transfer.name.sometimes' => 'this name is sometimes',
                    'Transfer.address.sometimes' => 'this address is sometimes',
                    'Transfer.city.sometimes' => 'this phone is sometimes',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                     $validate->errors()
                    , Response::HTTP_OK
                );
            }

            $validated_data = $validate->validated();
            $transfer_data = $validated_data['transfer'];

            $transfer = $this->transferService->updateTransfer($transfer_data, $transfer_id, $from_location_id, $to_location_id);

            return response()->success(
                [
                    'message' => 'Transfer updated successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );

        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_OK
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $transfer_id = request('transfer_id');
            $transferService = $this->transferService->delete($transfer_id);

            return response()->success(
                [
                    'message' => 'Product deleted successfully'
                ]
                ,Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_OK
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $transfer_id = request('transfer_id');
            $transferService = $this->transferService->forceDelete($transfer_id);

            return response()->success(
                [
                    'message' => 'Product deleted successfully'
                ]
                ,Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_OK
            );
        }
    }
}
