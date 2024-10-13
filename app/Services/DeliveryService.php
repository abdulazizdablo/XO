<?php

namespace App\Services;

use App\Models\FcmToken;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\StockLevel;
use App\Models\Transaction;
use Exception;
use App\Traits\FirebaseNotificationTrait;
use App\Enums\Roles;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class DeliveryService
{
	use FirebaseNotificationTrait;
	
    public function getAllDeliveryOrders($employee, $type, $date =null){
		$today = now()->format('Y-m-d');
		if(isset($date)){
			$today = $date;		
		}
		
		//return $today;
		//return (string)$today;
		//return Order::whereHas('shipment', function ($query) use ($today) {
		//		$query->where('date', $today);
		//	})->get();
		//return Carbon::today()->format('Y-m-d');
        $employee_account = $employee->account;
        if (!$employee_account){
            throw new Exception('Employee does not have any account');
        }
        $role = $employee_account->roles->first();

        if(!$role){
            throw new Exception('Employee does not have any role');
        }

        //check if the employee role is delivery_boy
        $role = $employee->account->roles->first()->name;

        if (!$role || $role != 'delivery_boy'){
            throw new Exception('you do not have the permission');
        }
        if($type == 'in_progress'){

            $orders = Order::with(['shipment'=>function($query){
                $query->select(['order_id','city','street','neighborhood','type','lat','long']);
            }, 'user'=>function($query){
                $query->select(['id','first_name','last_name','phone']);
            }])->whereHas('shipment', function ($query) use ($today) {
				$query->where('date',$today);	
			})->where([['employee_id', $employee->id],['status','processing'],['shipping_date','!=',null]])->select(['id','user_id','address_id','employee_id','invoice_number','status','delivery_type','paid_by_user','covered_by_gift_card','discounted_by_coupon','total_price','shipping_fee','payment_method','delivery_date'])->get();
			foreach($orders as $order){
				//$order->append(['priceWithFees','replaceOrderPriceDifferenceWithFees']);	
				$order->append('priceWithFees');
				if($order->delivery_type != null){
					$order->append('replaceOrderPriceDifferenceWithFees');
				}
			}
   
        }
        else{
            $orders = Order::with(['shipment'=>function($query){
               $query->select(['order_id','city','street','neighborhood','type', 'lat','long']);
            }, 'user'=>function($query){
                $query->select(['id','first_name','last_name','phone']);
            }])->whereHas('shipment', function ($query) use ($today) {
				$query->where('date',$today);	
			})->where([['employee_id', $employee->id],['delivery_date','!=',null]])->whereIn('status',['in_delivery','processing'])->select(['id','user_id','address_id','employee_id','invoice_number','status','delivery_type','paid_by_user','covered_by_gift_card','discounted_by_coupon','total_price','shipping_fee','payment_method','delivery_date'])->get();
		  foreach($orders as $order){
				//$order->append(['priceWithFees','replaceOrderPriceDifferenceWithFees']);	
				$order->append('priceWithFees');
				if($order->delivery_type != null){
					$order->append('replaceOrderPriceDifferenceWithFees');
				}
			}
        }
		//echo($orders->where('payment_method','cod'));
         //get the orders with the related models

        //calculate the collected and expected amounts
        $collected = 0;
        $expected = 0;
        foreach($orders as $order){
			if($order->payment_method == 'cod'){
		    	$expected += ($order->paid_by_user + $order->shipping_fee); 
			}
        }
        $all_orders = Order::whereHas('shipment', function ($query) use ($today) {
				$query->where('date',$today);	
			})->where('employee_id', $employee->id)->whereIn('status',['in_delivery','processing'])->get()->count();

         //get the delivered orders
         $delivered_orders = Order::whereHas('shipment', function ($query) use ($today) {
				$query->where('date',$today);	
			})->where([['employee_id', $employee->id],['status','received']])->get();
         foreach($delivered_orders as $delivered_order){
			 if($delivered_order->payment_method == 'cod'){
		    	$collected += ($delivered_order->paid_by_user + $delivered_order->shipping_fee);
			}
             
         }
         $delivered_orders_count = $delivered_orders->count();
 
         //return the data as an array
         return [
             'orders'=>$orders,
             'all_orders'=>$all_orders,
             'delivered_orders_count'=>$delivered_orders_count,
             'collected'=>$collected,
             'expected'=>$expected
         ];
    }

    public function getOrdersDashboard($employee, $type, $filter_data, $sort_data ){
         // $employee_account = $employee->account;
        if ($employee->hasRole(Roles::DELIVERY_ADMIN)){
            if($type == 'open'){
                $orders = Order::where('closed',0);
            }
            elseif($type == 'replace'){
                $orders = Order::where('status','replace');
            }
            elseif($type == 'received'){
                $orders = Order::where('status','received');
                
            }
            else{
                $orders = Order::query();
            }        
            if (!$orders) {
                throw new Exception('There Is No Orders Available');
            }
            // return $orders;
            $orders = $orders->with(
                [
                    'user:id,first_name,last_name,phone',
                    'shipment:id,order_id,city,street,neighborhood,type'
                ]
            )->select('id', 'user_id', 'inventory_id', 'address_id', 'invoice_number', 'total_price', 'total_quantity', 'type', 'status', 'closed', 'payment_method', 'created_at');



            if (!empty($sort_data)) {
                $orders = $this->applySort($orders, $sort_data);
            }
            
            return $orders->paginate(6);
        }else{
            throw new Exception('You do not have the permission',403);    
        }
    }
    
    public function getMainPageDeliveries($employee, $type, $order_num){
		$today = now()->format('Y-m-d');
        $employee_account = $employee->account;
        
        if (!$employee_account){
            throw new Exception('Employee does not have any account');
        }
        $role = $employee_account->roles->first();

        if(!$role){
            throw new Exception('Employee does not have any role');
        }

        //check if the employee role is delivery_boy
        $role = $employee->account->roles->first()->name;

        if (!$role || $role != 'delivery_boy'){
            throw new Exception('you do not have the permission');
        }
        $orderQuery = Order::with(['shipment' => function($query){
            $query->select(['order_id', 'city', 'street', 'neighborhood','type', 'lat', 'long']);
        }, 'user' => function($query){
            $query->select(['id', 'first_name', 'last_name', 'phone']);
        }]);
        
        $orderQuery->where([['employee_id', $employee->id], ['delivery_date', '!=', null]]);
        $orderQuery->whereHas('shipment', function ($query) use ($today) {
				$query->where('date',$today);	
			});
        if ($order_num) {
            $orderQuery->where('invoice_number','LIKE','%'.$order_num.'%')
            ->whereIn('status', ['in_delivery', 'processing']);
        } else {
            // Apply the type-specific constraints
            if ($type == 'in_progress') {
                $orderQuery->where('status', 'in_delivery');
            } else {
                $orderQuery->whereIn('status', ['in_delivery', 'processing']);
            }
        }

        $orderQuery->select(['id', 'user_id', 'address_id', 'employee_id', 'invoice_number', 'status', 'delivery_type', 'paid_by_user', 'total_price', 'payment_method', 'delivery_date']);

        $orders = $orderQuery->get();


        $coordinates = $orders->map(function ($order) {



            return [
                'lat' => $order->shipment->lat,
                'long' => $order->shipment->long
            ];
        })->toArray();

        return [
            'coordinates'=>$coordinates,
            'orders'=>$orders,
         ];    
    }

    public function getDeliveryOrder($employee, $order_id){
		
        //check if the employee role is delivery_boy
        $role = $employee->account->roles->first()->name;
        if (!$role || $role != 'delivery_boy'){
            throw new Exception('you do not have the permission');
        }

        $order = Order::findOrFail($order_id);
       
       if($order->delivery_type == null){
           //get the order with the related models
           $order = Order::with(['shipment','branch:id,name','order_items',
           'order_items:id,order_id,price,quantity,product_variation_id',
           'order_items.product_variation.color','order_items.product_variation.size','order_items.product_variation.product.main_photos:id,product_id,thumbnail,main_photo','order_items.product_variation.product:id,name'])
           ->where([['employee_id', $employee->id],['id',$order_id]])
           ->select(['id','user_id','address_id','employee_id','branch_id','invoice_number','payment_method','paid_by_user',
                        'total_quantity','total_price','price_without_offers','discounted_by_coupon','shipping_fee','is_gift','delivery_type'])
           ->first();
           if ($order) {
               $order->append(['discount','priceWithFees','reason','userFullName']);
           }

       }elseif($order->delivery_type == 'replacing'){
           $order = Order::with(['shipment', 'order_items' => function ($query) {
               $query->whereIn('status', ['return', 'new']);
           }, 'order_items.product_variation.color', 'order_items.product_variation.size','order_items.product_variation.product.main_photos:id,product_id,thumbnail,main_photo','order_items.product_variation.product:id,name'])
           ->where([['employee_id', $employee->id], ['id', $order_id]])
           ->select(['id', 'user_id', 'address_id', 'employee_id', 'branch_id', 'invoice_number','payment_method', 'paid_by_user',
                     'total_quantity', 'total_price','price_without_offers','discounted_by_coupon','shipping_fee', 'delivery_type'])
           ->first(); 
           if ($order) {
               $order->append(['discount','priceWithFees','replaceOrderPriceDifferenceWithFees', 'reason']);
           }
       }elseif($order->delivery_type == 'return'){
            $order = Order::with(['shipment', 'order_items' => function ($query) {
                $query->whereIn('status', ['return']);
            }, 'order_items.product_variation.color', 'order_items.product_variation.size','order_items.product_variation.product.main_photos:id,product_id,thumbnail,main_photo','order_items.product_variation.product:id,name'])
            ->where([['employee_id', $employee->id], ['id', $order_id]])
            ->select(['id', 'user_id', 'address_id', 'employee_id', 'branch_id', 'invoice_number', 'paid_by_user',
                    'total_quantity', 'total_price','price_without_offers','shipping_fee', 'delivery_type'])
            ->first(); 
            if ($order) {
                $order->append(['returnOrderPrice','returnOrderPriceMinusFees', 'reason']);
            }
       }
        //return the order as a model
        return $order;
   }

    public function confirmOrderIsDelivered($request, $employee){
        //check if the employee role is delivery_boy
        $role = $employee->account->roles->first()->name;
        if (!$role || $role != 'delivery_boy'){
            throw new Exception('you do not have the permission');
        }
        $order = Order::findOrFail($request->order_id);
           
        if($order->status == 'received'){
            throw new Exception('order already marked as delivered');
        }
        
		if ($order->paid == 0 && $order->payment_method != 'cod'){
			throw new Exception('Order is not paid yet');
		}
		
		$refund_transaction = Transaction::where([['transaction_uuid','refund'],['order_id','!=',null],['status','pending'],['payment_method','cod']])->first();
		
		if($refund_transaction){
			$refund_transaction->update(['status','completed']);	
		}
		
        $user = $order->user()->first();
		$fcm_tokens = $user->fcm_tokens()->pluck('fcm_token')->toArray(); 
		
        if ($order->status == 'in_delivery'){
            $order->update(['status'=>'received',
                            'receiving_date'=> now(),
						    'delivery_type'=> null]);
			if($order->paid == 0 && $order->payment_method == 'cod'){
				Transaction::create([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'transaction_uuid' => 'cod',
                    'operation_type' => 'create-order',
                    'payment_method' => 'cash on delivery',
                    'amount' => $order->paid_by_user + $order->shipping_fee,
                    'status' => 'completed', // Assuming you have a transaction_id column in your Transaction model
                    // 'token' => $token, // Assuming you want to store the token in the Transaction model
                ]);
                $order->update([
                    'paid'=>1
                    ]);
            }
			$original = Order::find($order->original_order_id);
			
			if($original){
				$original->update(['status'=>'replaced']);	
			}
			
			$shipment = $order->shipment()->update([
				'is_delivered' =>1
			]);
			$order_items = $order->order_items()->get();
			foreach($order_items as $order_item){
				if($order_item->status == 'return'){
					$order_item->forceDelete();
				}
				$order_item->update(['status'=>null]);	
			}
			$groupedItems = $order_items->groupBy(function ($item) {
                return $item->product_variation_id . '-' . $item->order_id . '-' . $item->reason;
            });            
            // Iterate over each group
            foreach ($groupedItems as $group) {
                // Calculate the total quantity and price for the group
                $totalQuantity = $group->sum('quantity');
                $original_price = $group->sum('original_price');
                $totalPrice = $group->sum('price');
            
                // Assuming you want to keep the first item in the group and update its quantity and price
                $firstItem = $group->first();
                $firstItem->quantity = $totalQuantity;
                $firstItem->original_price = $original_price;
                $firstItem->price = $totalPrice;
                $firstItem->save();

                // Delete the other items in the group
                if ($firstItem->quantity == 0) {
                    // If the first item's quantity is 0, delete the first item
                    $firstItem->forceDelete();
                } else {
                    // If the first item's quantity is not 0, delete the remaining items in the group
                    $group->slice(1)->each(function ($item) {
                        $item->forceDelete();
                    });
                }
            }
			$title = ["ar"=>"تم توصيل الطلب",
                "en"=>"Order delivered"];
            $body = ["ar"=>"نتمنى أن تكون العملية قد نالت استحسانك",
                "en"=>"Order delivered, Enjoy!!"];
			
			foreach($fcm_tokens as $fcm){
				$fcm_token = FcmToken::where([['fcm_token', $fcm],['user_id',$user->id]])->first();
				if($fcm_token->lang == 'en'){
						$this->send_notification($fcm, 
												 'Order delivered',
												 'Order delivered, Enjoy!!', 
												 'Order', 
												 'flutter_app'); // key source	
					}else{
						$this->send_notification($fcm, 
												 'تم توصيل الطلب',
												 'نتمنى أن تكون العملية قد نالت استحسانك',
												 'Order', 
												 'flutter_app'); // key source
					}	
				}

				$user->notifications()->create([
					'user_id'=>$user->id,
					'type'=> 'Order', // 1 is to redirect to the orders page
					'title'=>$title,
					'body'=>$body
				]);
			}
        }

    public function getAllDeliveryBoys($employee, $sort_data, $inventory_id){
        $employee_account = $employee->account;
        if (!$employee_account){
            throw new Exception('Employee does not have any account');
        }
        $role = $employee_account->roles->first();
        if(!$role){
            throw new Exception('Employee does not have any role');
        }
        // $role = $employee->account->roles->first()->name;
        // if (!$role || $role != 'main_admin') {
        //     throw new Exception('you do not have the permission');
        // }
        $query = Employee::whereHas('account.roles', function($query){
            $query->where('name','delivery_boy');
        })
        // ->with('account.roles')
        ->withCount(['orders' => function ($query) {
            $query->where('status', 'received');
        }])->withCount('orders as all_orders')->withSum('orders as amount', 'paid_by_user')->with('inventory.city');
        if ((isset($sort_data['sort_key']) && isset($sort_data['sort_value'])) && ($sort_data['sort_key'] != null && $sort_data['sort_value'] != null)) {
            $query = $this->applySort($query, $sort_data);
        }
        if($inventory_id){
            $query = $query->where('inventory_id', $inventory_id);
        }
        $data = $query->paginate(12);
		$inventories = Inventory::select('id', 'name')->get();
        //return $reports;
		return [
                    'inventories' => $inventories,
                    'current_page' => $data->currentPage(),
                    'data' => $data->items(),
                    'first_page_url' => $data->url(1),
                    'from' => $data->firstItem(),
                    'last_page' => $data->lastPage(),
                    'last_page_url' => $data->url($data->lastPage()),
                    'links' => $data->links(),
                    'next_page_url' => $data->nextPageUrl(),
                    'path' => $data->path(),
                    'per_page' => $data->perPage(),
                    'prev_page_url' => $data->previousPageUrl(),
                    'to' => $data->lastItem(),
                    'total' => $data->total(),
                ];
    } 

    public function getDeliveryHistory($employee, $date=null)
    {
        $employee_account = $employee->account;
		/*$today = now()->format('Y-m-d');
		
		if(isset($date)){
			$today = $date;		
		}*/
		
        if (!$employee_account){
            throw new Exception('Employee does not have any account');
        }
        $role = $employee_account->roles->first();

        if(!$role){
            throw new Exception('Employee does not have any role');
        }

        //check if the employee role is delivery_boy
        $role = $employee->account->roles->first()->name;

        if (!$role || $role != 'delivery_boy'){
            throw new Exception('you do not have the permission');
        }
        $orders = Order::with(['shipment'=>function($query){
           $query->select(['order_id','city','street','neighborhood','type', 'lat','long']);
        }, 'user'=>function($query){
            $query->select(['id','first_name','last_name','phone']);
        }])->when($date, function($query) use ($date){
			$query->whereHas('shipment', function ($query) use ($date) {
				$query->where('date',$date);
			});
		})->where([['employee_id', $employee->id],['delivery_date','!=',null]])->whereNotIn('status',['in_delivery','processing'])->select(['id','user_id','address_id','employee_id','invoice_number','status','delivery_type','paid_by_user','total_price','payment_method','delivery_date'])->get();
  
        $all_orders = $orders->count();

         //get the delivered orders
         $replaced_orders_count = Order::where([['employee_id', $employee->id],['status','replaced']])
			 ->when($date, function($query) use ($date){
				 $query->whereHas('shipment', function ($query) use ($date) {
				 	$query->where('date',$date);
			});
		})->count();
         $received_orders_count = Order::where([['employee_id', $employee->id],['status','received']]) 
			 ->when($date, function($query) use ($date){
				 $query->whereHas('shipment', function ($query) use ($date) {
				 	$query->where('date',$date);
			});
		})->count();
         $returned_orders_count = Order::where([['employee_id', $employee->id],['status','returned']])
			  ->when($date, function($query) use ($date){
				 $query->whereHas('shipment', function ($query) use ($date) {
				 	$query->where('date',$date);
			});
		})->count();
         $canceled_orders_count = Order::where([['employee_id', $employee->id],['status','canceled']])
			  ->when($date, function($query) use ($date){
				 $query->whereHas('shipment', function ($query) use ($date) {
				 	$query->where('date',$date);
			});
		})->count();

         //return the data as an array
         return [
             'orders'=>$orders,
             'all_orders'=>$all_orders,
             'replaced_orders_count'=>$replaced_orders_count,
             'received_orders_count'=>$received_orders_count,
             'returned_orders_count'=>$returned_orders_count,
             'canceled_orders_count'=>$canceled_orders_count,
         ];

        //check if the employee role is delivery_boy
        $role = $employee->account->roles->first()->name;
        if (!$role || $role != 'delivery_boy') {
            throw new Exception('you do not have the permission');
        }

        //get the orders with the related models
        $orders = Order::with(['shipment' => function ($query) {
            $query->select(['order_id', 'city', 'street', 'neighborhood', 'type',]);
        }, 'user' => function ($query) {
            $query->select(['id', 'first_name', 'last_name', 'phone']);
        }])->where([['employee_id', $employee->id],['paid',1]])->select(['id', 'user_id', 'address_id', 'employee_id', 'invoice_number', 'paid_by_user'])->get();

        return $orders;
    }
    
    public function getOrderBoys($order_id, $employee){
        try {
            if ($employee->hasRole(Roles::WAREHOUSE_ADMIN)||$employee->hasRole(Roles::DELIVERY_ADMIN)){
                $order = Order::findOrFail($order_id);
                $invoice = $order->invoice_number;
                $city = $order->inventory->city;
                $ship_type = $order->type;
                $delivery_boys = Employee::withCount([
                    'orders as all_count' => function ($query) {
                        $query->whereIn('status', ['received','processing']);
                    },
                    'orders as finished_count' => function ($query) {
                        $query->where('status', 'received');
                    }
                ])->whereHas('account.roles', function ($query) {
                    $query->where('name', 'delivery_boy');
                })->where('inventory_id', $order->inventory_id)->get();
                return [
                    'ship_type'=> $ship_type,
                    'city'=>$city,
                    'invoice'=>$invoice,
                    'delivery_boys'=>$delivery_boys,
                    ];
                
            }else{
                throw new Exception('You do not have the permission');
            }
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }  
    }
    
	public function getDeliveryBoy($boy_id, $employee){
        try {
            if ($employee->hasRole(Roles::DELIVERY_ADMIN)){
                return Employee::withCount('orders as all_orders')->findOrFail($boy_id);
            }else{
                throw new Exception('You do not have the permission');
            }
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }      
    }
	
    public function getOrdersByBoy($boy_id, $employee){
		if(!$boy_id){
		throw new Exception('Select Delivery boy first');
		}
        try {
            if ($employee->hasRole(Roles::DELIVERY_ADMIN)){
                return Order::where('employee_id',$boy_id)->with(['shipment:id,order_id,receiver_first_name,receiver_last_name,receiver_phone,city,street,neighborhood,type'])->paginate(12);
                // return Employee::with(['orders','orders.shipment:id,order_id,receiver_first_name,receiver_last_name,receiver_phone,city,street,neighborhood'])->withCount('orders as all_orders')->findOrFail($boy_id);
            }else{
                throw new Exception('You do not have the permission');
            }
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }  
    }
	
	public function customNotification($title, $body, $delivery_id, $employee){
        try {
            if ($employee->hasRole(Roles::DELIVERY_ADMIN)){
                $delivery = Employee::where('id',$delivery_id)->firstOrFail();
                if($delivery->hasRole(Roles::DELIVERY_BOY)){
                    $fcm_tokens = $delivery->fcm_tokens()->pluck('fcm_token')->toArray(); 
                    foreach($fcm_tokens as $fcm){
                        $this->send_notification($fcm,$title, $body,  'notification', 'delivery_app');
                    }
                    $notification = $delivery->notifications()->create([
						'employee_id' => $delivery->id,
						'type' => 'notification', // 1 is to redirect to the orders page
						"title" =>[
                    		"ar" => $title,
							"en" => $title
							],
						"body" =>[
							"ar" => $body,
                    		"en" => $body
							]
					]);
					return $notification;
                } else{
                    throw new Exception('Please Choose a delivery boy');
                }
            }else{
                throw new Exception('You do not have the permission');
            }
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }     
    }

        public function assignOrder($order_id, $delivery_id, $employee){
        try {
            if ($employee->hasRole(Roles::WAREHOUSE_MANAGER)||$employee->hasRole(Roles::DELIVERY_ADMIN)){
                $order = Order::findOrFail($order_id);
				$shipment_date = Carbon::parse($order->shipment->date)->format('Y-m-d');
				$current_date = Carbon::now()->format('Y-m-d');
				//return $shipment_date;
                $delivery = Employee::where('id',$delivery_id)->firstOrFail();

                if($delivery->hasRole(Roles::DELIVERY_BOY)){
                    $order->update([
                        'employee_id'=>$delivery->id,
                        'delivery_date'=>now(),
                    ]);
						if ($shipment_date === $current_date) {
						$fcm_tokens = $delivery->fcm_tokens()->pluck('fcm_token')->toArray(); 

						$title = ["en" => "New order assigned to you", "ar" => "تم اسناد طلب جديد"];

						$body = ["en" => "New order assigned to you, please check your order list",
								 "ar" => "تم اسناد طلب جديد، الرجاء مراجعة قائمة الطلبات المسندة"];
						foreach($fcm_tokens as $fcm){
							$fcm_token = FcmToken::where([['fcm_token', $fcm],['employee_id',$delivery->id]])->first();
							if($fcm_token->lang == 'en'){
								$this->send_notification($fcm, 
														 'New order assigned to you',
														 'New order assigned to you, please check your order list',
														 'new_order', 
														 'delivery_app'); // key source	
							}else{
								$this->send_notification($fcm, 
														 'تم اسناد طلب جديد',
														 'تم اسناد طلب جديد، الرجاء مراجعة قائمة الطلبات المسندة',
														 'new_order', 
														 'delivery_app'); // key source
							}	
						}

							//$this->send_notification($fcm,'تم اسناد طلب جديد','New order assigned to you','تم اسناد طلب جديد، الرجاء مراجعة قائمة الطلبات المسندة','New order assigned to you, please check your order list','new_order', 'delivery_app');
						$delivery->notifications()->create([
							'employee_id'=>$delivery->id,
							'type'=> 'new_order', // 1 is to redirect to the orders page
							'title'=>$title,
							'body'=>$body
						]);
					}
                } else{
                    throw new Exception('Please Choose a delivery boy');
                }
            }else{
                throw new Exception('You do not have the permission');
            }
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }     
    }
	
	public function cancelDelivery($employee, $request){
		try{
			DB::beginTransaction();
			$order = Order::where('employee_id',$employee->id)->findOrFail($request->order_id);
			if($order->status != 'in_delivery'){
				throw new Exception('You can not cancel this delivery now');
			}
			else {
				$order->update(['status'=>'canceled']);
				$order_items = $order->order_items()
					->where('status','new')->get();
				$returned_order_items = $order->order_items()
					->where('status','return')->get();
				$canceled = $order->order_items()->get();
				foreach($canceled as $c){
					$c->update([
						'status' => 'cancelled'
					]);		
				}
				foreach($order_items as $order_item){
					$stock_level = StockLevel::where([['inventory_id',$order_item->to_inventory],['product_variation_id',$order_item->product_variation_id]])->first();
					if(!$stock_level){
						$stock_level = StockLevel::create([
							'product_variation_id' => $order_item->product_variation_id,
							'inventory_id' => $order_item->to_inventory,
							'name' => Str::random(5),
							'min_stock_level' => 3,
							'max_stock_level' => 1000,
							'target_date' => now(),
							'sold_quantity' => 0,
							'status' => 'slow-movement',
							'current_stock_level' => 0
						]);	
					}

					//if ($order->payment_method == 'cod') {
					if ($order->paid == 1 || $order->payment_method == 'cod') {
						$stock_level->update([
							'current_stock_level' => $stock_level->current_stock_level + $order_item->quantity,
							'sold_quantity' => $stock_level->sold_quantity - $order_item->quantity
						]);
					} else {
						$stock_level->update([
							'current_stock_level' => $stock_level->current_stock_level + $order_item->quantity,
							'on_hold' => $stock_level->on_hold - $order_item->quantity
						]);
					}
					//$order_item->delete();
				}

				foreach($returned_order_items as $returned_order_item){
					$return_stock_level = StockLevel::where([['inventory_id',$returned_order_item->original_inventory],['product_variation_id',$returned_order_item->product_variation_id]])->first();	
					$return_stock_level->update([
						//'current_stock_level' => $return_stock_level->current_stock_level - $returned_order_item->quantity,
						'sold_quantity' => $return_stock_level->sold_quantity + $returned_order_item->quantity
					]);
					$current_return_stock_level = StockLevel::where([['inventory_id',$returned_order_item->to_inventory],['product_variation_id',$returned_order_item->product_variation_id]])->first();	
					$current_return_stock_level->update([
						'current_stock_level' => $current_return_stock_level->current_stock_level - $current_return_stock_level->quantity,
						//'sold_quantity' => $return_stock_level->sold_quantity + $returned_order_item->quantity
					]);
				}

				$paid_by_user = $order->paid_by_user;
				$fees = $order->shipping_fee;
				$gift_id = $order->gift_id;
				$covered_by_gift_card = 0;
				if($gift_id){
					$covered_by_gift_card = $order->covered_by_gift_card;
					$coupon = Coupon::where('type','gift')->findOrFail($gift_id);
					$amount_off = $coupon->amount_off;
					$new_amount = $covered_by_gift_card + $amount_off;
					$coupon->update([
						'amount_off' => Crypt::encryptString($new_amount),
					]);
				}

				if($order->original_order_id != null){
					$original_order = Order::findOrFail($order->original_order_id);
					$original_items = $original_order->order_items()->get();
					foreach($original_items as $original_item){
						$original_item->update(['status' => null]);	
					}
					$original_order->update(['status'=>'received']);
				}

				if($order->payment_method == 'cod' || $order->payment_method == 'Free' ){

					DB::commit();
					return "Order was canceled successfully";	
				}
				// return $coupon_password = Crypt::decryptString($coupon->password);
				// return $amount_off = Crypt::decryptString($coupon->amount_off);

				elseif($order->paid == 1){
					Transaction::create([
						'transaction_uuid' => 'refund',
						'order_id'=> $order->id,
						'user_id' => $order->user_id,
						'amount' => $paid_by_user + $fees,
						'status' => 'pending',
						'payment_method'=> $order->payment_method,
						'operation_type' =>'cancel_order'
					]);
					DB::commit();
					return "Order was canceled successfully";	
				}

				/*if($order->original_order_id != null){
						$original_order = Order::findOrFail($order->original_order_id);
						$original_order->update(['status'=>'received']);
					}*/
				DB::commit();
				return "Order was canceled successfully";	
			}
		}catch(Exception $th){
			DB::rollBack();
			throw new Exception($th->getMessage());
		}
	}
	
    public function startDelivery($order_id, $employee){
        try {
            $order = Order::findOrFail($order_id)->load('user');
			$user = $order->user;
			$fcm_tokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();
            if ($employee->hasRole(Roles::DELIVERY_BOY) && $order->employee_id == $employee->id){
                    $order->update([
                        'shipping_date'=>now(),
                        'status'=> 'in_delivery'
                    ]);
                $title = ["ar"=>"تم الآن بدء التوصيل",
						  "en"=>"Delivery started"];
                $body = ["ar"=>"بدأت الآن عملية التوصيل، سوف يتواصل معك عامل التوصيل قريبا",
                		  "en"=>"Delivery started now, Delivery boy will contact you soon"];
				foreach($fcm_tokens as $fcm){
					$fcm_token = FcmToken::where([['fcm_token', $fcm],['user_id',$user->id]])->first();
					if($fcm_token->lang == 'en'){
						$this->send_notification($fcm, 
												 'Delivery started',
												 'Delivery started now, Delivery boy will contact you soon',
												 'Order',
												 'flutter_app'); // key source	
					}else{
						$this->send_notification($fcm, 
												 'تم الآن بدء التوصيل',
												 'بدأت الآن عملية التوصيل، سوف يتواصل معك عامل التوصيل قريبا',
												 'Order',
												 'flutter_app'); // key source
					}	
				}
				
				$user->notifications()->create([
					'user_id'=>$user->id,
					'type'=> 'Order', // 1 is to redirect to the orders page
					'title'=>$title,
					'body'=>$body
				]);
            }else{
                throw new Exception('You do not have the permission');
            }
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }   
    }


    protected function applySort($query, array $sort_data)
    {
        return $query->orderBy($sort_data['sort_key'], $sort_data['sort_value']);
    }
}
