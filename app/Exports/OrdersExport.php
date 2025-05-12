<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;

class OrdersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
	protected OrderService $orderService; 

	public function __construct()
	{
		$this->orderService = app()->make(OrderService::class);
	}

	/**
     * @return \Illuminate\Support\Collection
     */
	public function collection()
	{
		$orders = Order::query()->where(function ($query) {
			$query->where([['original_order_id', null],['status','!=', 'replaced']])
				->where(function ($query) {
					$query->where('paid', 1)
						->orWhere(function ($query) {
							$query->where('paid', 0)
								->where('payment_method', 'cod');
						});
				})
				->orWhere(function ($query) {
					$query->where('original_order_id', '!=', null)
						->where('paid', 1)
						->orWhere(function ($query) {
							$query->where('paid', 0)
								->where('payment_method', 'cod');
						});
				});
		});
		$orders = $orders->orderBy('created_at', 'desc');
		$orders = $orders->with(
			[
				'shipment:id,order_id,receiver_first_name,receiver_last_name,receiver_phone,date,city,street,neighborhood',
				// 'user:id,first_name,last_name,phone',
			]
		)->select('id', 'user_id', 'inventory_id', 'invoice_number', 'total_price', 'total_quantity', 'type', 'status', 'closed', 'payment_method', 'created_at')->get();
		//Log::debug('orders: '. $orders->get());
		$modifiedOrders = $orders->map(function ($order) {
			$item = '';
			foreach($order->order_items as $i){
				$item = $item . '{' . $i->SkuCode . ' , quantity = '.$i->quantity . '}';
			}
			
			
        // Remove certain attributes
        //unset($user['attribute_to_remove']);
        
        // Transform data
        $order['shipping_date'] = $order['shipment']->date;
        $order['shipping_address'] = $order['shipment']->city . ' - '. $order['shipment']->street;
        $order['receiver'] = $order['shipment']->receiver_first_name .' '. $order['shipment']->receiver_last_name;
		$order['items'] = $item;
        unset($order['shipment']);

        return $order;
    });
		return $orders;

		//return $this->userService->getAllUsers([], [], null, null);
	}

	/**
     * Write code on Method
     *
     * @return response()
     */
	public function headings(): array
	{
		return [
			"id",
			"user id",
			"inventory id",
			"invoice number",
			"total price",
			"total quantity",
			"type",
			"status",
			"closed",
			"payment method",
			"created_at",
			"shipment date",
			"Shipment address",
			"Receiver",
			"items"
		];
	}
}
