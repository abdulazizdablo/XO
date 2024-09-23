<?php

namespace App\Http\Resources;

use App\Models\OrderItem;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrdersCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = auth('sanctum')->user();
        if(!$user){
            return response()->json('Unauthorized',403);
        }
        $user_id = $user->id;
        // return $this->collection ;

        $user = User::find($user_id);
        if ($this->resource instanceof AbstractPaginator) {
            $pagination = [
                "current_page" => $this->currentPage(),
                "first_page_url" =>  $this->getOptions()['path'] . '?' . $this->getOptions()['pageName'] . '=1',
                "prev_page_url" =>  $this->previousPageUrl(),
                "next_page_url" =>  $this->nextPageUrl(),
                "last_page_url" =>  $this->getOptions()['path'] . '?' . $this->getOptions()['pageName'] . '=' . $this->lastPage(),
                "last_page" =>  $this->lastPage(),
                "per_page" =>  $this->perPage(),
                "total" =>  $this->total(),
                "path" =>  $this->getOptions()['path'],
            ];
        }else{
            $pagination = null;
        }
        return [
            'orders' => $this->collection->transform(function ($item) use ($user) {
				$order_items = OrderItemResource::collection($item->order_items);
				$shipment = $item->shipment;
				$links = Setting::where('key','links')->firstOrFail();
				$phone = json_decode($links->value,true)['phone'];
				
                return [
                    'user' => $user,
                  
                    'order_id' => $item->id,
                    'user_id' => $item->user_id,
                    'packaging_id' => $item->packaging_id,
                    'feedback_id' => $item->feedback_id,
                    'coupon_id' => $item->coupon_id,
                    'delivery_date' => $item->delivery_date,
                    'created_at' => $item->created_at,
                    'employee' => $item->employee,
                    'address' => $item->address,
                    //'total_price' => $item->total_price,
                    'total_price' => $item->total_price,
                    'total_quantity' => $item->total_quantity,
                    'invoice_number' => $item->invoice_number,
                    'receiving_date' => $item->receiving_date,
                    'paid' => $item->paid,
                    'status' => $item->status,
                    'payment_method' => $item->payment_method,
                    'need_packaging' => $item->need_packaging,
                    'shipping_fee' => $item->shipping_fee,
                    'qr_code' => $item->qr_code,
                    'order_items' => $order_items,
                    'shipment' => $shipment,
					'mob' => $phone
                ];

            }

    ),


            'pagination' => $pagination,
        ];
    }


    public function with($request)
    {

    }
}
