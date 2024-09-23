<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\TransferItem;
use App\Services\TransferItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class TransferItemController extends Controller
{


    public function __construct(
        protected  TransferItemService $transferItemService
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
        $transfer_items = $this->transferItemService->getAllTransferItems();

        return response()->success([
            'transfer_items' => $transfer_items
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
            $transfer_id = request('transfer_id');
            $validate = Validator::make($request->all(),
                [
                    'transfer_item.quantity' => 'required|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                          $validate->errors()

                    , Response::HTTP_BAD_REQUEST
                );
            }

            $validated_data = $validate->validated();
            $transfer_item_data = $validated_data['transfer_item'];

            $transfer_item = $this->transferItemService->createTransferItem($transfer_item_data, $transfer_id);

            return response()->success(
                [
                    'message'=>'TransferItem Is Created',
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_BAD_REQUEST
            );
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TransferItem  $transfer_item
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $transfer_item_id = request('transfer_item_id');
            $transfer_item = $this->transferItemService->getTransferItem($transfer_item_id);

            return response()->success([
                'transfer_item' => $transfer_item],
            Response::HTTP_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                 Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TransferItem  $transfer_item
     * @return \Illuminate\Http\Response
     */
    public function edit(TransferItem $transfer_item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TransferItem  $transfer_item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $transfer_id = request('transfer_id');
            $transfer_item_id = request('transfer_item_id');

            $validate = Validator::make($request->all(),
                [
                    'transfer_item.quantity' => 'required|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(

                          $validate->errors()
                    
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $validated_data = $validate->validated();
            $transfer_item_data = $validated_data['transfer_item'];

            $transfer_item = $this->transferItemService->updateTransferItem($transfer_item_data, $transfer_item_id, $transfer_id);

            return response()->success(
                [
                    'message' => 'TransferItem updated successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );

        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TransferItem  $transfer_item
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $transfer_item_id = request('transfer_item_id');
            $transferItemService = $this->transferItemService->delete($transfer_item_id);

            return response()->success(
                [
                    'message' => 'Product deleted successfully'
                ]
                ,Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TransferItem  $transfer_item
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $transfer_item_id = request('transfer_item_id');
            $transferItemService = $this->transferItemService->forceDelete($transfer_item_id);

            return response()->success(
                [
                    'message' => 'Product deleted successfully'
                ]
                ,Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_NOT_FOUND
            );
        }
    }
}
