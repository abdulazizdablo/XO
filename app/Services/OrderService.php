<?php

namespace App\Services;

use App\Http\Resources\OrderResource;
use App\Http\Resources\OrdersCollection;
use App\Models\Coupon;
use App\Models\City;
use App\Models\Employee;
use App\Models\FcmToken;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\StockLevel;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use InvalidArgumentException;
use App\Utils\PaginateCollection as UtilsPaginateCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stripe\StripeClient;
use App\Notifications\ProductNotification;
use App\Notifications\OrderNotification;
use App\Traits\DistanceCalculator;
use App\Traits\FirebaseNotificationTrait;
use DateInterval;
use App\Enums\Roles;
use Illuminate\Support\Facades\Crypt;
use App\Enums\Inventories;
use App\Exceptions\OutOfStockException;
use Illuminate\Support\Facades\Log;

class OrderService
{
	use DistanceCalculator;
	use FirebaseNotificationTrait;

	public function __construct(
		protected UtilsPaginateCollection $pagainte
	) {
	}
	
	public function getInventoryIdByPoint($lat, $long){
		$inventories = Inventory::pluck('polygon','id');
		$point = [$lat, $long];
		foreach ($inventories as $inventoryId => $polygonJson) {
			// Decode the JSON polygon to an array
			$polygon = json_decode($polygonJson, true);

			// Check if the point is inside this polygon
			if ($this->pointInPolygon($point, $polygon)) {
				return $inventoryId;
			}
		}

		// If the point is not inside any polygon, return false
		return null;
	}
	
	public function pointInPolygon(array $point, array $polygon): bool {
		$x = $point[0];
		$y = $point[1];
		$inside = false;
		$numPoints = count($polygon);

		for ($i = 0, $j = $numPoints - 1; $i < $numPoints; $j = $i++) {
			$xi = $polygon[$i][0];
			$yi = $polygon[$i][1];
			$xj = $polygon[$j][0];
			$yj = $polygon[$j][1];

			$intersect = (($yi > $y) != ($yj > $y)) &&
				($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1) + $xi);
			if ($intersect) {
				$inside = !$inside;
			}
		}
		return $inside;
	}

	public function getAllOrders($filter_data, $sort_data, $type)
	{
		try {
			if ($type == 'open') {
				$orders = Order::query()->opened();
			} elseif ($type == 'fulfilled') {
				$orders = Order::query()->fulfilled();
			} else {
				$orders = Order::query();
			}
			$orders = $orders->with(
				[
					'address',
					'user:id,first_name,last_name,address,phone',
				]
			)->select('id', 'user_id', 'inventory_id', 'address_id', 'invoice_number', 'total_price', 'total_quantity', 'status', 'closed', 'payment_method', 'created_at');

			if (!$orders) {
				throw new InvalidArgumentException('There Is No Orders Available');
			}

			if (!empty ($filter_data)) {
				$orders = $this->applyFilters($orders, $filter_data);
			}

			if (!empty ($sort_data)) {
				$orders = $this->applySort($orders, $sort_data);
			}

			return $orders->paginate(6);
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}


	public function getOrdersByWarehouseAdmin($employee, $type, $filter_data, $sort_data, $request)
	{
		// $employee_account = $employee->account;
		if ($employee->hasRole(Roles::WAREHOUSE_ADMIN) || $employee->hasRole(Roles::WAREHOUSE_MANAGER) || $employee->hasRole(Roles::DELIVERY_ADMIN) || $employee->hasRole(Roles::MAIN_ADMIN)) {
			if ($employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
				// If the employee has the WAREHOUSE_MANAGER role, use their inventory_id
				$inventory_id = $employee->inventory_id;
			} else {
				if ($request->has('inventory_id')) {
					// If inventory_id is set, use it
					$inventory_id = $request->inventory_id;
				} else {
					// If inventory_id is not set, skip the inventory_id condition
					$inventory_id = null;
				}
			}

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



			if ($type == 'open') {
				//$orders = Order::where('closed', 0)->where(function ($query) {
				$orders = Order::where('closed', 0)->where(function ($query) {
					$query->where('paid', 1)
						->orWhere(function ($query) {
							$query->where('paid', 0)
								->where('payment_method', 'cod');
						});
				});
			} elseif ($type == 'replace') {
				$orders = Order::where([['original_order_id', '!=', null],['delivery_type','replacing']])->where(function ($query) {
					$query->where('paid', 1)
						->orWhere(function ($query) {
							$query->where('paid', 0)
								->where('payment_method', 'cod');
						});
				});
			} elseif ($type == 'received') {
				$orders = Order::where('status', 'received')->where(function ($query) {
					$query->where('paid', 1)
						->orWhere(function ($query) {
							$query->where('paid', 0)
								->where('payment_method', 'cod');
						});
				});
			} elseif ($type == 'fulfilled') {
				$orders = Order::where('status', 'fulfilled')->where(function ($query) {
					$query->where('paid', 1)
						->orWhere(function ($query) {
							$query->where('paid', 0)
								->where('payment_method', 'cod');
						});
				});
			} elseif ($type == 'returned') {
				$orders = Order::where([['original_order_id', '!=', null],['delivery_type','return']])->where(function ($query) {
					$query->where('paid', 1)
						->orWhere(function ($query) {
							$query->where('paid', 0)
								->where('payment_method', 'cod');
						});
				});
				//$orders = Order::with(['refunds', 'refunds.refund_items'])->where('status', 'returned');
			} elseif ($type == 'sub') {
				$orders = Order::where([['inventory_id','!=',$inventory_id],['status','!=','canceled']])
					->where(function ($query) {
						$query->where('paid', 1)
							->orWhere(function ($query) {
								$query->where('paid', 0)
									->where('payment_method', 'cod');
							});
					})
					->whereHas('order_items', function ($query) use ($inventory_id) {
						$query->where('on_hold', 1)
							->where('to_inventory', $inventory_id);
					})
					->with([
						'order_items' => function ($query) use ($inventory_id) {
							$query->where('on_hold', 1)
								->where('to_inventory', $inventory_id);
						}
					]);
				//$orders = Order::whereHas('order_items', function ($query) use ($inventory_id) {
				//$query->where('on_hold', 1)
				//->where('to_inventory', $inventory_id);
				//})->with([
				//          'order_items' => function ($query) use ($inventory_id) {
				//            $query->where('on_hold', 1)
				//              ->where('to_inventory', $inventory_id);
				//    }
				//]);
				////////////
				//$orders = $orders->where('inventory_id','!=',$inventory_id);
				// $orders = Order::with(['order_items' => function ($query) {
				//     $query->where('on_hold', 1);
				// }])->whereHas('order_items', function ($query) {
				//     $query->where('on_hold', 1);
				// })->whereIn('id', function ($query) use ($inventory_id) {
				//     $query->select('order_id')
				//           ->from('order_items')
				//           ->where([['to_inventory',$inventory_id],['on_hold',1]]);
				// });
			} elseif ($type == 'today') {
				$orders = Order::whereHas('shipment', function ($query) {
					$query->where([['express', 1], ['is_delivered', 0]])->orWhere([['date', Carbon::today()], ['is_delivered', 0]])->where(function ($query) {
						$query->where('paid', 1)
							->orWhere(function ($query) {
								$query->where('paid', 0)
									->where('payment_method', 'cod');
							});
					});
				});
			} elseif ($type == 'later') {
				$orders = Order::whereHas('shipment', function ($query) {
					$query->where([['express', 0], ['is_delivered', 0], ['date', '>', Carbon::today()]])->where(function ($query) {
						$query->where('paid', 1)
							->orWhere(function ($query) {
								$query->where('paid', 0)
									->where('payment_method', 'cod');
							});
					});
				});
			}
			if ($inventory_id !== null && $type !== 'sub') {
				$orders = $orders->where('inventory_id', $inventory_id);
			}
			if (!$orders) {
				throw new InvalidArgumentException('There Is No Orders Available');
			}
			// return $orders;
			$orders = $orders->orderBy('created_at', 'desc');
			$orders = $orders->with(
				[
					'shipment:id,order_id,receiver_first_name,receiver_last_name,receiver_phone,city,street,neighborhood',
					// 'user:id,first_name,last_name,phone',
				]
			)->select('id', 'user_id', 'employee_id', 'inventory_id', 'address_id', 'invoice_number', 'total_price', 'total_quantity', 'type', 'status', 'closed', 'payment_method', 'created_at');

			if (!empty ($filter_data)) {
				$orders = $this->applyFilters($orders, $filter_data);
			}

			if (!empty ($sort_data)) {
				$orders = $this->applySort($orders, $sort_data);
			}

			$paginatedOrders = $orders->paginate(6);
			foreach ($paginatedOrders as $order) {
				if($order->employee_id == null){
					$order->status = 'processing';
				}
			}
			if ($employee->hasRole(Roles::MAIN_ADMIN) || $employee->hasRole(Roles::WAREHOUSE_ADMIN)) {
				$inventories = Inventory::all();
				return [
					'current_page' => $paginatedOrders->currentPage(),
					'data' => $paginatedOrders->items(),
					'first_page_url' => $paginatedOrders->url(1),
					'from' => $paginatedOrders->firstItem(),
					'last_page' => $paginatedOrders->lastPage(),
					'last_page_url' => $paginatedOrders->url($paginatedOrders->lastPage()),
					'links' => $paginatedOrders->links(),
					'next_page_url' => $paginatedOrders->nextPageUrl(),
					'path' => $paginatedOrders->path(),
					'per_page' => $paginatedOrders->perPage(),
					'prev_page_url' => $paginatedOrders->previousPageUrl(),
					'to' => $paginatedOrders->lastItem(),
					'total' => $paginatedOrders->total(),
					'inventories' => $inventories,
				];
			} else {
				return [
					'current_page' => $paginatedOrders->currentPage(),
					'data' => $paginatedOrders->items(),
					'first_page_url' => $paginatedOrders->url(1),
					'from' => $paginatedOrders->firstItem(),
					'last_page' => $paginatedOrders->lastPage(),
					'last_page_url' => $paginatedOrders->url($paginatedOrders->lastPage()),
					'links' => $paginatedOrders->links(),
					'next_page_url' => $paginatedOrders->nextPageUrl(),
					'path' => $paginatedOrders->path(),
					'per_page' => $paginatedOrders->perPage(),
					'prev_page_url' => $paginatedOrders->previousPageUrl(),
					'to' => $paginatedOrders->lastItem(),
					'total' => $paginatedOrders->total(),
					'inventories' => Null,
				];
			}

		} else {
			throw new Exception('You do not have the permission', 403);
		}

	}

	public function getInventoriesCountsChart()
	{
		try {
			$counts = Inventory::with('city')->withCount('orders')->get();
			$totalOrders = $counts->sum('orders_count');

			$percentageCounts = $counts->map(function ($category) use ($totalOrders) {
				if ($totalOrders != 0) {
					$percentage = ($category->orders_count / $totalOrders) * 100;
				} else {
					$percentage = 0;
				}
				$category->percentage = $percentage;
				return $category;
			});

			$sortedCounts = $percentageCounts->sortByDesc('percentage');

			return $sortedCounts;
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}

	public function getOrderCards($employee, $inventory_id = null)
	{
		try {

			$dateScope = request('date_scope');
			$from_date = null;
			$to_date = null;
			if ($dateScope == null) {
				$dateScope == 'Today';
			}
			$orders = Order::all();
			$modelName = \App\Models\Order::class;
			if ($employee->hasRole(Roles::WAREHOUSE_ADMIN)||$employee->hasRole(Roles::DELIVERY_ADMIN)) {

				if($dateScope == 'Today' ){
					$query = Order::whereDate('created_at', '>=', Carbon::now()->startOfDay())
						->when($inventory_id, function ($query, $inventory_id) {
							return $query->where('inventory_id', $inventory_id);
						});
				}elseif($dateScope == 'last_week'){
					$query = Order::whereBetween('created_at', [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->startOfDay()])
						->when($inventory_id, function ($query, $inventory_id) {
							return $query->where('inventory_id', $inventory_id);
						});		
				}elseif($dateScope == 'last_month' ){
					$query = Order::whereDate('created_at', '>=', Carbon::now()->subMonth()->startOfDay())
						->when($inventory_id, function ($query, $inventory_id) {
							return $query->where('inventory_id', $inventory_id);
						});			
				}elseif($dateScope == 'last_year' ){
					$query = Order::whereDate('created_at', '>=', Carbon::now()->subYear()->startOfDay())
						->when($inventory_id, function ($query, $inventory_id) {
							return $query->where('inventory_id', $inventory_id);
						});
				}
				$all = $query->count();
				//$completed = $query->where('closed', 1)->count();
				//$unfinished = $query->where('closed', 0)->count();
				$completed = $query->where('status', 'received')->count();
				$unfinished = $query->whereIn('status', ['processing','in_delivery'])->count();
				/*$query = Order::scopeDateRange($orders, $modelName, $dateScope, $from_date, $to_date);
				// Apply the inventory_id condition only if $inventory_id is not null
				if ($inventory_id != null) {
					$query = $query->where('inventory_id', $inventory_id);
				}
				$all = $query->count();
				//$completed = $query->where('closed', 1)->count();
				//$unfinished = $query->where('closed', 0)->count();
				$completed = $query->where('status', 'received')->count();
				$unfinished = $query->whereIn('status', ['processing','in_delivery'])->count();*/
			} elseif ($employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
				$all = Order::scopeDateRange($orders, $modelName, $dateScope, $from_date, $to_date)->where('inventory_id', $employee->inventory_id)->count();
				$completed = Order::scopeDateRange($orders, $modelName, $dateScope, $from_date, $to_date)->where('inventory_id', $employee->inventory_id)->where('status', 'received')->count();
				$unfinished = Order::scopeDateRange($orders, $modelName, $dateScope, $from_date, $to_date)->where('inventory_id', $employee->inventory_id)->whereIn('status', ['processing','in_delivery'])->count();
			} else {
				throw new Exception('Unauthorized', 403);
			}
			return [
				'all' => $all,
				'completed' => $completed,
				'unfinished' => $unfinished
			];
		} catch (\Throwable $th) {
			throw $th;
		}
	}

	public function ordersCharts($filter_data)
	{
		try {
			$year = request('year');
			$month = request('month');
			$status1 = request('status1');
			$status2 = request('status2');

			if ($year != null && $month == null) {

				$allOrders = Order::count();
				$ordersExceptThisMonth = Order::where('created_at', '<', Carbon::now()->startOfDay())->count();
				if ($ordersExceptThisMonth == 0) {
					$percentageDifference = 0;
				} else {
					$percentageDifference = (($allOrders - $ordersExceptThisMonth) / $ordersExceptThisMonth) * 100;
				}
				$months = range(1, 12);

				$status1OrdersByMonth = DB::table('orders')
					->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
					->whereYear('created_at', $year)
					->where('status', $status1)
					->groupBy(DB::raw('MONTH(created_at)'))
					->get();


				$data1 = [];


				foreach ($months as $month) {
					$data1[$month] = 0;
				}


				foreach ($status1OrdersByMonth as $item) {
					$data1[$item->month] = $item->count;
				}

				$status2OrdersByMonth = DB::table('orders')
					->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
					->whereYear('created_at', $year)
					->where('status', $status2)
					->groupBy(DB::raw('MONTH(created_at)'))
					->get();

				$data2 = [];

				foreach ($months as $month) {
					$data2[$month] = 0;
				}

				foreach ($status2OrdersByMonth as $item) {
					$data2[$item->month] = $item->count;
				}
				return ['allOrders' => $allOrders, 'percentageDifference' => $percentageDifference, 'status1OrdersByMonth' => $data1, 'status2OrdersByMonth' => $data2];
			} elseif ($year != null && $month != null) {
				$allOrders = Order::count();
				$ordersExceptThisMonth = Order::where('created_at', '<', Carbon::now()->startOfMonth())->count();
				if ($ordersExceptThisMonth == 0) {
					$percentageDifference = 0;
				} else {
					$percentageDifference = (($allOrders - $ordersExceptThisMonth) / $ordersExceptThisMonth) * 100;
				}
				$daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;
				$data1 = [];
				$data2 = [];

				for ($day = 1; $day <= $daysInMonth; $day++) {
					$status1OrdersByDay = DB::table('orders')
						->select(DB::raw('DAY(created_at) as day'), DB::raw('COUNT(*) as count'))
						->whereYear('created_at', $year)
						->whereMonth('created_at', $month)
						->where('status', $status1)
						->whereDay('created_at', $day)
						->groupBy(DB::raw('DAY(created_at)'))
						->get();

					$status2OrdersByDay = DB::table('orders')
						->select(DB::raw('DAY(created_at) as day'), DB::raw('COUNT(*) as count'))
						->whereYear('created_at', $year)
						->whereMonth('created_at', $month)
						->where('status', $status2)
						->whereDay('created_at', $day)
						->groupBy(DB::raw('DAY(created_at)'))
						->get();

					$data1[$day] = $status1OrdersByDay->count();
					$data2[$day] = $status2OrdersByDay->count();
				}
				return ['allOrders' => $allOrders, 'percentageDifference' => $percentageDifference, 'data1' => $data1, 'data2' => $data2];
			}
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}

	public function getAllUserOrders($user_id, $filter_data, $sort_data, $page_size=5)
	{

		//$user = User::findOrFail($user_id);

		$orders = User::findOrFail($user_id)->orders()->where(function ($query) {
			$query->where('paid', 1)
				->orWhere(function ($query) {
					$query->where('paid', 0)
						->where('payment_method', 'cod');
				});
		})->where('replacedOrReturned',0)->with(
			'shipment',
			'order_items',
			'order_items.product_variation',
			'order_items.product_variation.product.photos',
			'order_items.product_variation.size',
			'order_items.product_variation.color'
		);
		$orders = $orders->orderBy('created_at', 'desc');
		//$orders = $orders->orderBy('created_at','Desc')

		if (!$orders) {
			throw new InvalidArgumentException('There Is No Orders Available');
		}

		if (!empty ($filter_data)) {
			$orders = $this->applyFilters($orders, $filter_data);
		}

		if (!empty ($sort_data)) {
			$orders = $this->applySort($orders, $sort_data);
		}
		//$orders = $orders->get();
		$orders = $orders->paginate($page_size);

		return OrdersCollection::make($orders);
	}

	public function getOrder($order_id)
	{
		//$order = Order::findOrFail($order_id)->load('order_items');

		// $product_variation_ids = [];
		//$order->order_items->each(function ($item) {


		//  $product_variation_ids[] = $item->product_variation_id;



		//  });




		//$products_variations = ProductVariation::findOrFail($product_variation_ids)->load('product.products_variations', 'product.pricing');

		//$product_models = $products_variations->map(function ($product_variation) {
		//    return $product_variation->product;
		//});



		//return $products =  ProductCollection::make($product_models)->sortBy('id')->values()->all();



		return $order = Order::find($order_id)->load([
			'employee:id,location_id,first_name,last_name,email,phone',
			'address:id,country,city,street,building,apartment',
			'order_items',
			'order_items.product_variation',
			'order_items.product_variation.product.photos',
			'order_items.product_variation.size',
			'order_items.product_variation.color',
		]);

		if (!$order) {
			throw new InvalidArgumentException('Order not found');
		}

		return OrderResource::make($order);
	}

	public function createOrder($order_data, $user_id, $address_id, $branch_id, $city_id, $inventory_id, $lat, $long)
	{
		if(!$inventory_id){
			$minDistance = PHP_INT_MAX; // a large number
			// $nearestInventory = null; // the inventory object with the minimum distance
			$inventory = Inventory::where('city_id', $city_id)->first();
			if (!$inventory) {
				$inventories = Inventory::get();
				foreach ($inventories as $inv) {
					$distance = $this->getDistance($inv->lat, $inv->long, $lat, $long);
					if ($distance < $minDistance) { // if the current inventory is closer than the previous one
						$minDistance = $distance; // update the minimum distance
						$inventory = $inv; // update the nearest inventory
					}
				}
			}	
		}else{
			$inventory = Inventory::find($inventory_id);	
		}

		if (isset ($order_data['coupon']) && $order_data['coupon'] != null) {
			// return $order_data['coupon'];
			$coupon = Coupon::where([['code', $order_data['coupon']], ['type', 'coupon']])->first();
			if (!$coupon) {
				throw new Exception('Coupon is unavailable');
			}
			// $total_price = $order_data['total_price'];
			if (!$coupon->isValid() && $coupon->remainUsingCounts() <= 0) {
				throw new Exception('Your Coupon Can not be used');
				// $total_price = ($order_data['total_price'] * $coupon->percentage) / 100;
			}
			;
		}

		if (isset ($order_data['gift_card']) && $order_data['gift_card'] != null) {
			if (!isset($order_data['gift_card_password'])){
				throw new Exception('Wrong Gift Card Password');
			}
			$gift_card = Coupon::where([['code', $order_data['gift_card']], ['type', 'gift']])->first();
			if (!$gift_card) {
				throw new Exception('Gift card is unavailable');
			}
			// $total_price = $order_data['total_price'];
			if (!$gift_card->isValid() && $gift_card->amount_off <= 0) {
				throw new Exception('Your gift card Can not be used');
				// $total_price = ($order_data['total_price'] * $coupon->percentage) / 100;
			}
			if ($gift_card != null) {
				// Assuming $coupon is retrieved from a database or similar
				if (!$gift_card && Hash::check( $order_data['gift_card_password'],$gift_card->password)) {
					throw new Exception('Wrong Password');
				}
			}
		}

		$fees = 0; //to do: get fees from settings
		$free_shipping = 10000000;
		$key = Setting::where('key', 'fees')->first();

		if ($key != null) {
			$key->value = json_decode($key->value);
			$fees = $key['value']->en->shipping_fee;
			$free_shipping = $key['value']->en->free_shipping;
		}
		$type = 'xo-delivery';
		if($branch_id != null){
			$type = 'kadmous';
		}
		//to do: get user total orders value so if it more than the setting of free shipping then fees will be zero
		$order_data['address_id'] = $address_id;
		$order_data['user_id'] = $user_id;
		// return ($order_data);
		// $order = Order::create([$order_data]);
		// if($)
		$order = Order::create([
			'user_id' => $user_id,
			'branch_id' => $branch_id ?? null,
			'type' => $type,
			'address_id' => $address_id,
			// 'type' => $order_data['type'],
			'packaging_id' => null,
			'coupon_id' => $coupon->id ?? null,
			'gift_id' => $gift_card->id ?? null,
			'address_id' => $address_id ?? null,
			'is_gift' => $order_data['is_gift']??0,
			'gift_message'=> $order_data['gift_message']??null,
			'total_price' => 0,
			'total_quantity' => 0,
			'payment_method' => $order_data['payment_method'],
			'shipping_fee' => $fees,
			'qr_code' => $order_data['qr_code'],
			'inventory_id' => $inventory->id,
		]);



		if (!$order) {
			throw new InvalidArgumentException('Something Wrong Happend');
		}

		/*	if ($type === 'kadmous'){
		throw new  InvalidArgumentException(' Al Kadmous Service is unavailable right now');
		}
		*/
		return $order;
	}

	public function createOrderItem($order_id, $order_items_data, $city, $inventory_id)
	{
		$total_price = 0;
		$total_quantity = 0;

		/*$order_items_offers = $order_items_data;

		$order_items_offers->filter(function($item) use(&$order_items_offers){

return $order_items_offers->where('group_id',$item->group_id)->count() < 2;





                })->each(function($item){

                    $bogo_items = $order_items_offers->where('group_id',$item->group_id)->where('promotion_type','bogo')->sortBy('price')->get();
               // Assuming $order_items_offers is your collection
$bogo_items = $order_items_offers
    ->where('group_id', $item->group_id)
    ->where('promotion_type', 'bogo')
    ->sortBy('price')
    ->get();

// Add the is_free property to each item, setting it to true for the first item and false for the rest
$bogo_items->transform(function ($item, $key) {
    $item->is_free = $key === 0; // The first item will have $key === 0
    return $item;
});

 $btgo_items = $order_items_offers->where('group_id',$item->group_id)->where('promotion_type','btgo')->sortBy('price')->get();
$btgo_items->transform(function ($item, $key) {
    $item->is_free = $key === 0; // The first item will have $key === 0
    return $item;
});
  $bogh_items = $order_items_offers->where('group_id',$item->group_id)->where('promotion_type','bogh')->sortBy('price')->get();
    $btgo_items = $order_items_offers->where('group_id',$item->group_id)->where('promotion_type','btgo')->sortBy('price')->get();





                    for($i=1 ,$i<count($order_items_offers),$i++){




                        if($order_items_offers->where('group_id', $order_items_offers[$i]->group_id)->count() < 2 ){

                        $order_items_data[$i]->is_free = false;
                            break;

                        }

                            else  {

                        if($order_items_data[$i])


                            }




                });
*/


		// Group the order items data by product variation ID
		$aggregatedExchangeItems = array_reduce($order_items_data, function ($carry, $item) {
			$productId = $item['product_variation_id'];

			if (!isset($carry[$productId])) {
				$carry[$productId] = ['quantity' => 0];
			}

			$carry[$productId]['quantity'] += $item['quantity'];
			return $carry;
		}, []);

		$transformedExchangeItems = []; // we are doing this transformation because sometimes the same product variation is sent more than once in the same exchange request, and without handling it, only the first apperance will be calculated.
		foreach ($aggregatedExchangeItems as $productId => $details) {
			$transformedExchangeItems[] = [
				'product_variation_id' => $productId,
				'quantity' => $details['quantity'],
			];
		}
		$product_variations_data = Collection::make($transformedExchangeItems)->groupBy('product_variation_id');
		// Extract the product variation IDs and product IDs as separate arrays
		$product_variation_ids = array_column($order_items_data, 'product_variation_id');

		$grouped_product_variations = ProductVariation::whereIn('id', $product_variation_ids)
			->orderByRaw("FIELD(id, " . implode(',', $product_variation_ids) . ")")
			->with([
				'product',
				'product.pricing:id,product_id,currency,value,location',
				'product.discount:id,percentage,start_date,end_date',
				'group',
				'group.offers',
				'group.discounts',
				'stock_levels',
			])
			->get()
			->groupBy(function ($item) {

				if ($item->group && $item->group->valid == 1 && (Str::lower($item->group->promotion_type) == 'bogo' || Str::lower($item->group->promotion_type) == 'bogh' || Str::lower($item->group->promotion_type) == 'btgo' || Str::lower($item->group->type == 'discount'))) {
					// Retrieve the first offer associated with the group and get its type
					// return Str::lower($item->group->offers()->first()->type);
					if($item->group->promotion_type == 'flash_sales'){
						return Str::lower('discount');
					}
					return Str::lower($item->group->promotion_type);
				} else {
					// If the product variation does not belong to a group, assign it to 'no_group'
					return 'no_group';
				}
			});
		// return $grouped_product_variations;
		// Iterate over the grouped product variations and process each group
		foreach ($grouped_product_variations as $key => $product_variations) {  
			// Check the stock levels of the product variations
			$stock = $this->checkStockLevel($product_variations, $product_variations_data, $city, $inventory_id);

			// Generate the method name based on the group key
			$method = 'calculate' . Str::studly(str_replace('_', '', Str::upper($key)));

			if ($stock['available']) {
				switch ($key) {
					case 'bogo':
						// Calculate the BOGO (Buy One Get One) offer
						if (method_exists($this, $method)) {
							$order_items_data = $this->calculateBOGO($product_variations, $product_variations_data, $order_id, $stock);
							$total_price += $order_items_data['price'];
							$total_quantity += $order_items_data['total_quantity'];
						}

						break;

					case 'btgo':
						// Calculate the BTGO (Buy Two Get One) offer
						if (method_exists($this, $method)) {
							$order_items_data = $this->calculateBTGO($product_variations, $product_variations_data, $order_id, $stock);
							$total_price += $order_items_data['price'];
							$total_quantity += $order_items_data['total_quantity'];

						}

						break;

					case 'bogh':
						// Calculate the BOGH (Buy One Get Half) offer
						if (method_exists($this, $method)) {
							$order_items_data = $this->calculateBOGH($product_variations, $product_variations_data, $order_id, $stock);
							$total_price += $order_items_data['price'];
							$total_quantity += $order_items_data['total_quantity'];
						}

						break;

					case 'discount':
						if (method_exists($this, $method)) {
							$order_items_data = $this->calculateDiscount($product_variations, $product_variations_data, $order_id, $stock);
							$total_price += $order_items_data['price'];
							$total_quantity += $order_items_data['total_quantity'];

						}

						break;

					case 'pair':
						// Calculate the pair offer
						// $this->calculatePair();
						break;

					case 'no_group':
						// Calculate the offer for product variations without a group
						if (method_exists($this, $method)) {
							$order_items_data = $this->calculateNOGROUP($product_variations, $product_variations_data, $order_id, $stock);
							$total_price += $order_items_data['price'];
							$total_quantity += $order_items_data['total_quantity'];

						}

						break;

					default:
						// Default case if no specific offer type is matched
						break;
				}
			}
		}
		return [
			'total_price' => $total_price,
			'total_quantity' => $total_quantity
		];
		// Return the created order items

	}

	public function checkAvailableInCity($order_items_data, $city)
	{
		$available = 1;
		foreach ($order_items_data as $order_items) {
			$quantity = $order_items['quantity'];
			$product_variation = ProductVariation::find($order_items['product_variation_id']);
			if(!$product_variation){
				throw new Exception('Sorry, Products are not available any more', 400);		
			}
			$stock_levels_in_city = $product_variation->stock_levels()->whereHas('inventory', function ($query) use ($city) {
				$query->where('city_id', $city);
			})->first();
			if (!isset ($stock_levels_in_city)) {
				throw new Exception('Stock unavailable', 400);
			}
			// return $quantity;
			// return $stock_levels_in_city->current_stock_level;
			if ($quantity > $stock_levels_in_city->current_stock_level) {
				$available = 0;
			}
		}
		return $available;
	}

	public function checkAvailable($order_items_data)
	{
		$available = 1;
		//	throw new Exception('Sorry, Products are not available any more', 400);	

		foreach ($order_items_data as $order_items) {
			$quantity = $order_items['quantity'];
			$product_variation = ProductVariation::find($order_items['product_variation_id']);

			if(!$product_variation){
				throw new Exception('Sorry, Products are not available any more', 400);		
			}

			if(!$product_variation->product->name){
				throw new Exception('Sorry, Products are not available any more', 400);			
			}

			$quantity_all_over = $product_variation->stock_levels()->sum('current_stock_level');
			if ($quantity > $quantity_all_over) {
				$locale = app()->getLocale();
				if ($locale == 'ar'){



					throw new Exception($product_variation->product->name . ' غير متوفر حاليا');	
				}else{
					throw new Exception($product_variation->product->name . ' is out of stock');	
				}
			}
			// $stock_levels_in_city = $product_variation->stock_levels()->whereHas('inventory', function ($query) use ($city) {
			//     $query->where('city_id', $city);})->first();
			//     if(!isset($stock_levels_in_city)){
			//         throw new Exception('Stock unavailable', 400);
			//     }
			//     // return $quantity;
			//     // return $stock_levels_in_city->current_stock_level;
			// if ($quantity > $stock_levels_in_city->current_stock_level) {
			//     $available = 0;
			// }
			$out_of_stock = true;
			$stocks_outside_city = $product_variation->stock_levels()->get();

			foreach ($stocks_outside_city as $outside_city) {
				if ($quantity <= $outside_city->current_stock_level) {
					$out_of_stock = false;
					break; // Stock level found, return early
				}
			}
			if ($out_of_stock){
				$locale = app()->getLocale();
				if($quantity == 1){
					if ($locale == 'ar'){
						throw new Exception($product_variation->product->name . ' غير متوفر حاليا');	
					}else{
						throw new Exception($product_variation->product->name . ' is out of stock');	
					}	
				}else{
					if ($locale == 'ar'){
						throw new Exception('لا يمكن اتمام طلب '.$product_variation->product->name . ' وفق الكمية المطلوبة، حاول مرة ثانية بعد انقاص الكمية');	
					}else{
						throw new Exception($product_variation->product->name . ' quantity is not available, try again after reducing the selected amount');	
					}		
				}	
			}
			if ($out_of_stock) {
				$locale = app()->getLocale();
				if ($locale == 'ar'){
					throw new Exception($product_variation->product->name . ' غير متوفر حاليا');	
				}else{
					throw new Exception($product_variation->product->name . ' is out of stock');	
				}
			}
		}
		return $available;
	}

	public function checkStockLevel($product_variations, $product_variation_data, $city, $inventory_id)
	{
		$main_city = City::whereRaw('JSON_EXTRACT(name, "$.en") = ?', ['Aleppo'])->first();
		$hold_products = [];
		$hold_products_quantities = [];
		$source_inventory_ids = [];
		// Iterate over the product variations
		foreach ($product_variations as $product_variation) {
			$quantity_all_over = $product_variation->stock_levels()->sum('current_stock_level');
			$quantity = $product_variation_data[$product_variation->id]->first()['quantity']; // Get the quantity for the current product variation

			if ($quantity > $quantity_all_over) {
				$locale = app()->getLocale();
				if ($locale == 'ar'){
					throw new Exception($product_variation->product->name . ' غير متوفر حاليا');	
				}else{
					throw new Exception($product_variation->product->name . ' is out of stock');	
				}
			}

			// $stock_levels = $product_variation->stock_levels; // Get the stock levels associated with the product variation
			 $stock_levels_in_city = $product_variation->stock_levels()->whereHas('inventory', function ($query) use ($city) {
                $query->where('city_id', $city);
				            })->first();//->get(); we used first because we agreed that there is only one inventory in each city
			//$stock_levels_in_city = $product_variation->stock_levels()->where('inventory_id', $city)->first();
			$main_stock_level = $product_variation->stock_levels()->whereHas('inventory', function ($query) use ($main_city) {
				$query->where('city_id', $main_city->id);
			})->first();// we agreed that the main inventory is in Aleppo
			// Iterate over the stock levels
			if ($stock_levels_in_city && ($quantity <= $stock_levels_in_city->current_stock_level)) {			
				$stock_levels_in_city->update(
					[
						'on_hold' => $stock_levels_in_city->on_hold + $quantity,
						'current_stock_level' => $stock_levels_in_city->current_stock_level - $quantity
					]
				);
				array_push($hold_products, $product_variation->id);				
				array_push($hold_products_quantities, $quantity);				
				array_push($source_inventory_ids, $stock_levels_in_city->inventory_id);

				continue; // Stock level found, return early
			} elseif ($main_stock_level && ($quantity <= $main_stock_level->current_stock_level)) {
				$main_stock_level->update(
					[
						'on_hold' => ($main_stock_level->on_hold + $quantity),
						'current_stock_level' => $main_stock_level->current_stock_level - $quantity
					]
				);
				array_push($hold_products, $product_variation->id);
				array_push($hold_products_quantities, $quantity);
				array_push($source_inventory_ids, $main_stock_level->inventory_id);
				continue;
			} else {
				$out_of_stock = true;
				$stocks_outside_city = $product_variation->stock_levels()->whereHas('inventory', function ($query) use ($city) {
					$query->where('city_id', '!=', $city);
				})->get();

				foreach ($stocks_outside_city as $outside_city) {
					if ($quantity <= $outside_city->current_stock_level) {
						$outside_city->update(
							[
								'on_hold' => ($outside_city->on_hold + $quantity),
								'current_stock_level' => $outside_city->current_stock_level - $quantity
							]
						);
						array_push($hold_products, $product_variation->id);
						array_push($hold_products_quantities, $quantity);
						array_push($source_inventory_ids, $outside_city->inventory_id);
						$out_of_stock = false;
						break; // Stock level found, return early
					}
				}

			}

			if ($out_of_stock) {

				$locale = app()->getLocale();
				if ($locale == 'ar'){
					throw new Exception($product_variation->product->name . ' غير متوفر حاليا');	
				}else{
					throw new Exception($product_variation->product->name . ' is out of stock');	
				}
			}

		}
		return [
			'available' => true,
			'hold_products' => $hold_products,
			'hold_products_quantities' => $hold_products_quantities,
			'source_inventory_ids' => $source_inventory_ids
		];
	}


	public function calculateBOGO($product_variations, $product_variations_data, $order_id, $stock)
	{
		$holdProductsMap = array_combine($stock['hold_products'], $stock['source_inventory_ids']);

		$product_variations = collect($product_variations)
			->sortBy(function ($product_variation) {
				return $product_variation['product']['pricing']['value'];
			}) // Sort product variations by the pricing value of the associated product
			->flatten()->reverse(); // Flatten the collection

		$original_prices = [];
		$prices = [];
		$pvs = []; //array for product variations
		$gids = []; //array for group IDs
		$total_quantity = 0;
		// Iterate over the product variations
		foreach ($product_variations as $product_variation) {
			$quantity = (int) $product_variations_data[$product_variation->id]->first()['quantity']; // Get the quantity for the current product variation
			$total_quantity += $quantity;
			$product = $product_variation->product; // Get the associated product
			$pricing = $product->pricing; // Get the pricing value for the associated product
			for ($i = 0; $i < $quantity; $i++) {
				array_push($pvs, $product_variation->id);
				array_push($gids, $product_variation->group_id);
				array_push($original_prices, $pricing->value);
			}

			// $photo = $product->main_photos()->first()->path;
		}
		$gids_unique = array_unique($gids); // get unique gids
		$pvs_new = [];
		$original_prices_new = [];

		foreach ($gids_unique as $gid) {
			$pvs_new[$gid] = array_values(array_filter($pvs, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));

			$original_prices_new[$gid] = array_values(array_filter($original_prices, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));
		}
		foreach ($original_prices_new as $gid => $original_prices_gid) {
			$prices[$gid] = array_map(function ($value, $index) {
				if ($index % 2 == 1) {
					return 0;
				}
				return $value;
			}, $original_prices_gid, array_keys($original_prices_gid));
		}
		$orderItems = [];
		$total = 0;
		$holdProductsMap = array_combine($stock['hold_products'], $stock['source_inventory_ids']);

		foreach ($pvs_new as $key => $pvs) {
			$grouped = array_count_values($pvs);
			foreach ($grouped as $pv => $quantity) {
				$indices = array_keys($pvs, $pv);
				$price = array_sum(array_intersect_key($prices[$key], array_flip($indices)));
				$original_price = array_sum(array_intersect_key($original_prices_new[$key], array_flip($indices)));

				$orderItem = [
					'order_id' => $order_id,
					'product_variation_id' => $pv,
					'quantity' => $quantity,
					'price' => $price,
					'original_price' => $original_price,
					'group_id' => $key,
					'promotion_name' => 'bogo',
					'status' => 'new'
				];

				// Check if $pv exists in hold_products
				//$pv_id = $product_variations_data[$product_variation->id]->first()['product_variation_id'];
				$pv_id = $pv;

				if (array_key_exists($pv_id, $holdProductsMap)) {
					$orderItem['to_inventory'] = $holdProductsMap[$pv_id];
					$orderItem['original_inventory'] = $holdProductsMap[$pv_id];
					if (Order::where('id', $order_id)->firstOrFail()->inventory_id == $holdProductsMap[$pv_id]) {
						$orderItem['on_hold'] = false;
					} else {
						$orderItem['on_hold'] = true;
					}
				}

				$orderItems[] = $orderItem;
				$total += $price;
			}
		}
		foreach ($orderItems as $item) {
			OrderItem::create($item);
		}

		return [
			'price' => $total,
			'total_quantity' => $total_quantity
		];
	}

	public function calculateBTGO($product_variations, $product_variations_data, $order_id, $stock)
	{
		$product_variations = collect($product_variations)
			->sortBy(function ($product_variation) {
				return $product_variation['product']['pricing']['value'];
			}) // Sort product variations by the pricing value of the associated product
			->flatten()->reverse(); // Flatten the collection

		// $quantities = collect($product_variations_data)
		//     ->flatten(1)
		//     ->pluck('quantity');

		// // Calculate the total quantity using the 'sum' method
		// $total_quantity = $quantities->sum();
		$ids = $product_variations->pluck('id');
		$total_quantity = collect($product_variations_data)
			->flatten(1) // Flatten to combine all nested arrays
			->whereIn('product_variation_id', $ids) // Filter only matching IDs
			->sum('quantity');

		$limit = floor($total_quantity / 3);
		$original_prices = [];
		$prices = [];
		$pvs = [];
		$gids = [];
		foreach ($product_variations as $product_variation) {
			$quantity = (int) $product_variations_data[$product_variation->id]->first()['quantity']; // Get the quantity for the current product variation

			$total_price = 0; // Initialize the total price as 0
			$product = $product_variation->product; // Get the associated product
			$pricing = $product->pricing; // Get the pricing value for the associated product
			for ($i = 1; $i < $quantity + 1; $i++) {
				array_push($pvs, $product_variation->id);
				array_push($gids, $product_variation->group_id);
				// If the current length of the prices array is even, push the pricing value, else push 0
				// array_push($prices, $i % 3 == 0 ? 0 : $pricing->value);                
				array_push($original_prices, $pricing->value);
			}
			// $prices = array_map(function ($value, $index) {
			//     // Change the third element to 0
			//     if ($index % 3 == 2) {
			//         return 0;
			//     }
			//     return $value;
			// }, $original_prices, array_keys($original_prices));
			// array_push($prices, $pricing->value );
			if ($limit > $quantity) {
				$applied_quantity = $limit - $quantity;
				$total_price = 0; // Set the total price as 0 since the BOGO offer is fully applied
				$limit -= $quantity;
			} elseif ($limit <= $quantity && $limit > 0) {
				$applied_quantity = $quantity - $limit;
				$total_price = $pricing->value * $applied_quantity; // Calculate the total price based on the applied quantity and pricing value
			}

			// $photo = $product->main_photos()->first()->path;

			// array_push($stripe_array, [
			//     'price_data' => [
			//         'currency' => $pricing->currency,
			//         // 'tax_behavior' => 'inclusive',
			//         'product_data' => [
			//             'name' => $product->name,
			//             'images' =>  [url("$photo")]
			//         ],
			//         'unit_amount' => $total_price * 100
			//     ],
			//     'quantity' => $applied_quantity
			// ]);

			$productVariationData = $product_variations_data[$product_variation->id]->first();
			// return $productVariationData;
			$productVariationData['price'] = $total_price;
			$productVariationData['currency'] = $pricing->currency;

			$priced_product_variations[] = $productVariationData; // Add the priced product variation data to the result array
			$total_price = 0; // Reset the total price to 0 for the next iteration
		}

		$gids_unique = array_unique($gids); // get unique gids
		$pvs_new = [];
		$original_prices_new = [];

		foreach ($gids_unique as $gid) {
			$pvs_new[$gid] = array_values(array_filter($pvs, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));

			$original_prices_new[$gid] = array_values(array_filter($original_prices, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));
		}
		foreach ($original_prices_new as $gid => $original_prices_gid) {
			$prices[$gid] = array_map(function ($value, $index) {
				if ($index % 3 == 2) {
					return 0;
				}
				return $value;
			}, $original_prices_gid, array_keys($original_prices_gid));
		}
		$total = 0;
		$orderItems = [];
		$holdProductsMap = array_combine($stock['hold_products'], $stock['source_inventory_ids']);
		foreach ($pvs_new as $key => $pvs) {
			$grouped = array_count_values($pvs);
			foreach ($grouped as $pv => $quantity) {
				$indices = array_keys($pvs, $pv);
				$price = array_sum(array_intersect_key($prices[$key], array_flip($indices)));
				$original_price = array_sum(array_intersect_key($original_prices_new[$key], array_flip($indices)));

				$orderItem = [
					'order_id' => $order_id,
					'product_variation_id' => $pv,
					'quantity' => $quantity,
					'price' => $price,
					'original_price' => $original_price,
					'group_id' => $key,
					'promotion_name' => 'btgo',
					'status' => 'new'
				];

				// Check if $pv exists in hold_products
				$pv_id = $pv;
				if (array_key_exists($pv_id, $holdProductsMap)) {
					$orderItem['to_inventory'] = $holdProductsMap[$pv_id];
					$orderItem['original_inventory'] = $holdProductsMap[$pv_id];
					if (Order::where('id', $order_id)->firstOrFail()->inventory_id == $holdProductsMap[$pv_id]) {
						$orderItem['on_hold'] = false;
					} else {
						$orderItem['on_hold'] = true;
					}
				}

				$orderItems[] = $orderItem;
				$total += $price;
			}
		}
		foreach ($orderItems as $item) {
			OrderItem::create($item);
		}
		// foreach ($pvs_new as $key => $pvs) {
		//     $grouped = array_count_values($pvs);
		//     foreach ($grouped as $pv => $quantity) {
		//         $indices = array_keys($pvs, $pv);
		//         $price = array_sum(array_intersect_key($prices[$key], array_flip($indices)));
		//         $original_price = array_sum(array_intersect_key($original_prices_new[$key], array_flip($indices)));
		//         $orderItems[] = [
		//             'order_id' => $order_id,
		//             'product_variation_id' => $pv,
		//             'quantity' => $quantity,
		//             'price' => $price,
		//             'original_price' => $original_price
		//         ];
		//         $total += $price;
		//     }
		// }

		// return $prices;
		// // Loop through each pvs
		// foreach ($pvs as $index => $pv) {
		//     // If the pv is not in the grouped_data array, initialize it
		//     if (!isset($grouped_data[$pv])) {
		//         $grouped_data[$pv] = [
		//             'order_id' => $order_id,
		//             'product_variation_id' => $pv,
		//             'quantity' => 0,
		//             'price' => 0,
		//             'original_price' => 0
		//         ];
		//     }

		// Increment the quantity, price and original_price for the pv
		//     $grouped_data[$pv]['quantity']++;
		//     $grouped_data[$pv]['price'] += $prices[$index];
		//     $grouped_data[$pv]['original_price'] += $original_prices[$index];
		// }
		// return $grouped_data;

		// foreach ($grouped_data as $data) {
		//     $order_items[] = OrderItem::create([
		//         'order_id' => $order_id,
		//         'product_variation_id' => $data['product_variation_id'],
		//         // 'currency' => $order_item['currency'],
		//         'quantity' => $data['quantity'],
		//         'price' => $data['price'],
		//         'original_price' => $data['original_price']
		//     ]);
		// }
		// Now, you can loop through the grouped_data array to insert the data into the database
		// foreach ($grouped_data as $data) {
		//     $order->order_items()->attach($data['product_variation_id'], [
		//         'quantity' => $data['quantity'],
		//         'price' => $data['price'],
		//         'original_price' => $data['original_price']
		//     ]);
		// }

		return [
			'price' => $total,
			// 'price' => $price,
			'total_quantity' => $total_quantity
		];
		// return $priced_product_variations;
		// return $stripe_array;
		// return [
		//     "priced_product_variations" => $priced_product_variations,
		//     "stripe_array" => $stripe_array,
		// ]; // Return the array of priced product variations
	}

	public function calculateBOGH($product_variations, $product_variations_data, $order_id, $stock)
	{
		$product_variations = collect($product_variations)
			->sortBy(function ($product_variation) {
				return $product_variation['product']['pricing']['value'];
			}) // Sort product variations by the pricing value of the associated product
			->flatten()->reverse(); // Flatten the collection
		// $quantities = collect($product_variations_data)
		//     ->flatten(1)
		//     ->pluck('quantity');

		// // Calculate the total quantity using the 'sum' method
		// $total_quantity = $quantities->sum();
		$ids = $product_variations->pluck('id');
		$total_quantity = collect($product_variations_data)
			->flatten(1) // Flatten to combine all nested arrays
			->whereIn('product_variation_id', $ids) // Filter only matching IDs
			->sum('quantity');
		$limit = floor($total_quantity / 2);
		$original_prices = [];
		$prices = [];
		$pvs = [];
		$gids = [];
		// if(count($product_variations) == 1){   
		//     $quantity = (int) $product_variations_data[$product_variations[0]->id]->first()['quantity']; 

		// }
		foreach ($product_variations as $product_variation) {
			$quantity = (int) $product_variations_data[$product_variation->id]->first()['quantity']; // Get the quantity for the current product variation
			$total_price = 0; // Initialize the total price as 0
			$product = $product_variation->product; // Get the associated product
			$pricing = $product->pricing; // Get the pricing value for the associated product
			for ($i = 1; $i < $quantity + 1; $i++) {
				array_push($pvs, $product_variation->id);
				array_push($gids, $product_variation->group_id);
				// If the current length of the prices array is even, push the pricing value, else push 0
				// array_push($prices, $i % 3 == 0 ? 0 : $pricing->value);                
				array_push($original_prices, $pricing->value);
			}
			// $prices = array_map(function ($value, $index) use ($original_prices) {
			//     // Change the third element to 0
			//     if ($index % 2 == 1) {
			//         return $original_prices[$index]/2;
			//     }
			//     return $value;
			// }, $original_prices, array_keys($original_prices));
			//array_push($prices, $pricing->value );
			if ($limit > $quantity) {
				$applied_quantity = $limit - $quantity;
				$total_price = 0; // Set the total price as 0 since the BOGO offer is fully applied
				$limit -= $quantity;
			} elseif ($limit <= $quantity && $limit > 0) {
				$applied_quantity = $quantity - $limit;
				$total_price = $pricing->value * $applied_quantity; // Calculate the total price based on the applied quantity and pricing value
			}

			// $photo = $product->main_photos()->first()->path;

			// array_push($stripe_array, [
			//     'price_data' => [
			//         'currency' => $pricing->currency,
			//         // 'tax_behavior' => 'inclusive',
			//         'product_data' => [
			//             'name' => $product->name,
			//             'images' =>  [url("$photo")]
			//         ],
			//         'unit_amount' => $total_price * 100
			//     ],
			//     'quantity' => $applied_quantity
			// ]);

			$productVariationData = $product_variations_data[$product_variation->id]->first();
			// return $productVariationData;
			$productVariationData['price'] = $total_price;
			$productVariationData['currency'] = $pricing->currency;

			$priced_product_variations[] = $productVariationData; // Add the priced product variation data to the result array
			$total_price = 0; // Reset the total price to 0 for the next iteration
		}
		$gids_unique = array_unique($gids); // get unique gids

		$pvs_new = [];
		$original_prices_new = [];

		foreach ($gids_unique as $gid) {
			$pvs_new[$gid] = array_values(array_filter($pvs, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));

			$original_prices_new[$gid] = array_values(array_filter($original_prices, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));
		}
		foreach ($original_prices_new as $gid => $original_prices_gid) {
			$prices[$gid] = array_map(function ($value, $index) {
				if ($index % 2 == 1) {
					return $value / 2;
				}
				return $value;
			}, $original_prices_gid, array_keys($original_prices_gid));
		}
		// return $pvs_new;  
		$total = 0;
		$orderItems = [];
		$holdProductsMap = array_combine($stock['hold_products'], $stock['source_inventory_ids']);
		foreach ($pvs_new as $key => $pvs) {
			$grouped = array_count_values($pvs);
			foreach ($grouped as $pv => $quantity) {
				$indices = array_keys($pvs, $pv);
				$price = array_sum(array_intersect_key($prices[$key], array_flip($indices)));
				$original_price = array_sum(array_intersect_key($original_prices_new[$key], array_flip($indices)));

				$orderItem = [
					'order_id' => $order_id,
					'product_variation_id' => $pv,
					'quantity' => $quantity,
					'price' => $price,
					'original_price' => $original_price,
					'group_id' => $key,
					'promotion_name' => 'bogh',
					'status' => 'new'
				];

				// Check if $pv exists in hold_products
				$pv_id = $pv;
				if (array_key_exists($pv_id, $holdProductsMap)) {
					$orderItem['to_inventory'] = $holdProductsMap[$pv_id];
					$orderItem['original_inventory'] = $holdProductsMap[$pv_id];
					if (Order::where('id', $order_id)->firstOrFail()->inventory_id == $holdProductsMap[$pv_id]) {
						$orderItem['on_hold'] = false;
					} else {
						$orderItem['on_hold'] = true;
					}
				}

				$orderItems[] = $orderItem;
				$total += $price;
			}
		}
		foreach ($orderItems as $item) {
			OrderItem::create($item);
		}
		// foreach ($pvs_new as $key => $pvs) {
		//     $grouped = array_count_values($pvs);
		//     foreach ($grouped as $pv => $quantity) {
		//         $indices = array_keys($pvs, $pv);
		//         $price = array_sum(array_intersect_key($prices[$key], array_flip($indices)));
		//         $original_price = array_sum(array_intersect_key($original_prices_new[$key], array_flip($indices)));
		//         $orderItems[] = [
		//             'order_id' => $order_id,
		//             'product_variation_id' => $pv,
		//             'quantity' => $quantity,
		//             'price' => $price,
		//             'original_price' => $original_price
		//         ];
		//         $total += $price;
		//     }
		// }

		// Now you can store $orderItems in your database
		// foreach ($orderItems as $item) {
		//     OrderItem::create($item);
		// }

		// foreach ($original_prices_new as $original){
		//     return $original;
		// }
		// Loop through each pvs
		// foreach ($pvs_new as $gid => $pvs_gid) {
		//     $original_prices_gid = $original_prices_new[$gid];
		//     foreach ($pvs_gid as $index => $pv) {
		//         // If the pv is not in the grouped_data array, initialize it
		//         if (!isset($grouped_data[$pv])) {
		//             $grouped_data[$pv] = [
		//                 'order_id' => $order_id,
		//                 'product_variation_id' => $pv,
		//                 'quantity' => 0,
		//                 'price' => 0,
		//                 'original_price' => 0
		//             ];
		//         }

		//         // Increment the quantity, price and original_price for the pv
		//         $grouped_data[$pv]['quantity']++;
		//         $grouped_data[$pv]['price'] += $prices[$index];
		//         $grouped_data[$pv]['original_price'] += $original_prices_new[$index];
		//     }
		// }

		// foreach ($grouped_data as $data) {
		//     $order_items[] = OrderItem::create([
		//         'order_id' => $order_id,
		//         'product_variation_id' => $data['product_variation_id'],
		//         // 'currency' => $order_item['currency'],
		//         'quantity' => $data['quantity'],
		//         'price' => $data['price'],
		//         'original_price' => $data['original_price']
		//     ]);
		// }
		// Now, you can loop through the grouped_data array to insert the data into the database
		// foreach ($grouped_data as $data) {
		//     $order->order_items()->attach($data['product_variation_id'], [
		//         'quantity' => $data['quantity'],
		//         'price' => $data['price'],
		//         'original_price' => $data['original_price']
		//     ]);
		// }
		// return "done";
		// return $priced_product_variations;
		return [
			'price' => $total,
			// 'price' => $price,
			'total_quantity' => $total_quantity
		];
		// return $stripe_array;
		// return [
		//     "priced_product_variations" => $priced_product_variations,
		//     "stripe_array" => $stripe_array,
		// ]; // Return the array of priced product variations
	}

	public function cancelOrder($user_id, $order_id)
	{
		try{
			DB::beginTransaction();
			$order = Order::where('user_id',$user_id)->findOrFail($order_id);
			if($order->status != 'processing'){
				throw new Exception('You can not cancel the order now');
			}
			else {
				if($order->delivery_type == 'return'){
					$returned_order_items = $order->order_items()
						->where('status','return')->get();
				}
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
							//'sold_quantity' => $stock_level->sold_quantity - $order_item->quantity
						]);
						$original_stock_level = StockLevel::where([['inventory_id',$order_item->original_inventory],['product_variation_id',$order_item->product_variation_id]])->first();
						$original_stock_level->update(['sold_quantity' => $original_stock_level->sold_quantity - $order_item->quantity]);
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
					if(!$return_stock_level){
						$return_stock_level = StockLevel::create([
							'product_variation_id' => $returned_order_item->product_variation_id,
							'inventory_id' => $returned_order_item->to_inventory,
							'name' => Str::random(5),
							'min_stock_level' => 3,
							'max_stock_level' => 1000,
							'target_date' => now(),
							'sold_quantity' => 0,
							'status' => 'slow-movement',
							'current_stock_level' => 0
						]);		
					}
					$return_stock_level->update([
						//'current_stock_level' => $return_stock_level->current_stock_level - $returned_order_item->quantity,
						'sold_quantity' => $return_stock_level->sold_quantity + $returned_order_item->quantity
					]);
					$current_return_stock_level = StockLevel::where([['inventory_id',$returned_order_item->to_inventory],['product_variation_id',$returned_order_item->product_variation_id]])->first();
					if(!$current_return_stock_level){
						$current_return_stock_level = StockLevel::create([
							'product_variation_id' => $returned_order_item->product_variation_id,
							'inventory_id' => $returned_order_item->to_inventory,
							'name' => Str::random(5),
							'min_stock_level' => 3,
							'max_stock_level' => 1000,
							'target_date' => now(),
							'sold_quantity' => 0,
							'status' => 'slow-movement',
							'current_stock_level' => 0
						]);		
					}
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
						'user_id' => $user_id,
						'amount' => $paid_by_user + $fees,
						'status' => 'pending',
						'payment_method'=> $order->payment_method,
						//'transaction_source' => $order->transaction->transaction_source,
						'transaction_source' => '55',
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

	public function calculateDiscount($product_variations, $product_variations_data, $order_id, $stock)
	{
		// return $product_variations;
		$holdProductsMap = array_combine($stock['hold_products'], $stock['source_inventory_ids']);
		$orderItems = [];
		$total_quantity = 0;
		$total_price = 0;
		foreach ($product_variations as $product_variation) {
			$group_id = $product_variation->group_id;
			$percentage = $product_variation->group->percentage;
			$price = ceil((($product_variation->product->pricing->value) * (100 - $percentage)) / 100);
			$quantity = $product_variations_data[$product_variation->id]->first()['quantity'];
			$total_quantity += $quantity;
			$total_price += $price;
			$product = $product_variation->product;

			$orderItem = [
				'order_id' => $order_id,
				'product_variation_id' => $product_variations_data[$product_variation->id]->first()['product_variation_id'],
				'quantity' => $quantity,
				'price' => $price * $quantity,
				'original_price' => ($product_variation->product->pricing->value)*$quantity,
				'group_id' => $product_variation->group_id,
				'promotion_name' => 'discount',
				'status' => 'new'
			];

			// Check if product_variation_id exists in hold_products
			$pv_id = $product_variations_data[$product_variation->id]->first()['product_variation_id'];
			if (array_key_exists($pv_id, $holdProductsMap)) {
				$orderItem['to_inventory'] = $holdProductsMap[$pv_id];
				$orderItem['original_inventory'] = $holdProductsMap[$pv_id];
				if (Order::where('id', $order_id)->firstOrFail()->inventory_id == $holdProductsMap[$pv_id]) {
					$orderItem['on_hold'] = false;
				} else {
					$orderItem['on_hold'] = true;
				}
			}
			$orderItems[] = $orderItem;
		}

		foreach ($orderItems as $item) {
			OrderItem::create($item);
		}
		// $photo = $product->main_photos()->first()->path;
		return [
			'price' => $total_price,
			'total_quantity' => $total_quantity
		];
		// array_push($stripe_array, [
		//     'price_data' => [
		//         'currency' => $pricing->currency,
		//         // 'tax_behavior' => 'inclusive',
		//         'product_data' => [
		//             'name' => $product->name,
		//             'images' =>  [url("$photo")]
		//         ],
		//         'unit_amount' => $price * 100
		//     ],
		//     'quantity' => $quantity
		// ]);

		// $productVariationData = $product_variations_data[$product_variation->id]->first();
		// $productVariationData['price'] = $price * $quantity;
		// $productVariationData['currency'] = $pricing->currency;
		// array_push($product_variations_data[$product_variation->id]->first(), ['price' => $price * $quantity]);
		// array_push($product_variations_data[$product_variation->id]->first(), ['currency', $pricing->currency]);
		// $product_variation_data = Arr::add($product_variations_data[$product_variation->id]->first(), 'price', $price * $quantity);
		// $product_variation_data = Arr::add($product_variations_data[$product_variation->id]->first(), 'currency', $pricing->currency);

		//     $priced_product_variations[] = $productVariationData;
		// }

		// return [
		//     "priced_product_variations" => $priced_product_variations,
		//     "stripe_array" => $stripe_array,
		// ]; // Return the array of priced product variations

	}

	// Calculate No Group Offer
	public function calculateNOGROUP($product_variations, $product_variations_data, $order_id, $stock)
	{
		$product_variations_data;
		$total = 0;
		$holdProductsMap = array_combine($stock['hold_products'], $stock['source_inventory_ids']);
		$total_quantity = 0;
		$orderItems = [];
		foreach ($product_variations as $product_variation) {
			$quantity = $product_variations_data[$product_variation->id]->first()['quantity'];
			$price = ($product_variation->product->pricing->value)* $quantity;
			$total_quantity += $quantity;
			$product = $product_variation->product;
			$orderItem = [
				'order_id' => $order_id,
				'product_variation_id' => $product_variations_data[$product_variation->id]->first()['product_variation_id'],
				'quantity' => $quantity,
				'price' => $price,
				'original_price' => $price,
				'group_id' => null,
				'promotion_name' => null,
				'status' => 'new'
			];
			//if($holdProductsMap[$pv_id] == Order::where('id',$order_id)->firstOrFail()->inventory_id)
			//echo '   order inventory   '. Order::where('id',$order_id)->firstOrFail()->inventory_id;
			// Check if product_variation_id exists in hold_products
			$pv_id = $product_variations_data[$product_variation->id]->first()['product_variation_id'];
			if (array_key_exists($pv_id, $holdProductsMap)) {
				$orderItem['to_inventory'] = $holdProductsMap[$pv_id];
				$orderItem['original_inventory'] = $holdProductsMap[$pv_id];
				if (Order::where('id', $order_id)->firstOrFail()->inventory_id == $holdProductsMap[$pv_id]) {
					$orderItem['on_hold'] = false;
				} else {
					$orderItem['on_hold'] = true;
				}
			}

			$orderItems[] = $orderItem;
			$total += $price;
		}

		foreach ($orderItems as $item) {
			OrderItem::create($item);
		}

		// $photo = $product->main_photos()->first()->path;
		return [
			'price' => $total,
			// 'price' => $price,
			'total_quantity' => $total_quantity
		];
	}

	public function applyCouponAndGift($response, $order)
	{
		// first apply coupon
		// second apply gift card
		// add column to order to determine how much of total price was paid using the gift card
		$fees = $order->shipping_fee;
		$free_shipping = 10000000;
		$key = Setting::where('key', 'fees')->firstOrFail();

		if ($key != null) {
			$key->value = json_decode($key->value);
			$free_shipping = $key['value']->en->free_shipping;
		}

		if($response['total_price'] > $free_shipping){
			$fees = 0;	
		}

		$total_price = $response['total_price'];
		$total_quantity = $response['total_quantity'];
		$discounted_by_coupon = 0;
		$covered_by_gift_card = 0;
		$paid_by_user = $total_price;
		if ($order->coupon) {
			// $coupon = Coupon::find($order->coupon)->first();
			$coupon = $order->coupon;
			if ($coupon) {
				$percentage = $coupon->percentage;
				$total_price;
				$discounted_by_coupon = floor(($total_price * $percentage) / 100);//round up to keep values integer because flutter up deals only with integer right now
				$paid_by_user = $total_price - $discounted_by_coupon;
				$coupon->used_redemption += 1;
				if ($coupon->used_redemption >= $coupon->max_redemption) {
					$coupon->valid = 0;
				}
			}
		}

		if ($order->gift_id) {
			$gift_card = $order->gift;
			if ($gift_card) {
				if ($gift_card->amount_off >= ($paid_by_user + $fees)) {
					$covered_by_gift_card = $paid_by_user + $fees;
					$paid_by_user = 0;
					$new_amount = $gift_card->amount_off - $covered_by_gift_card ;
				} else {
					$covered_by_gift_card = $gift_card->amount_off;
					$paid_by_user = $paid_by_user - $covered_by_gift_card;
					$new_amount = $gift_card->amount_off - $covered_by_gift_card ;
				}
				//$new_amount = $gift_card->amount_off - $covered_by_gift_card - $fees ;
				$gift_card->amount_off = Crypt::encryptString($new_amount);
				$gift_card->save();
			}
		}

		$order->update([
			'total_price' => $total_price,
			'paid_by_user' => $paid_by_user,
			'covered_by_gift_card' => $covered_by_gift_card,
			'discounted_by_coupon' => $discounted_by_coupon,
			'total_quantity' => $total_quantity
		]);

		if($paid_by_user == 0){
			$new_product_variatins_ids = OrderItem::select(['id', 
															'product_variation_id', 
															'quantity', 
															'to_inventory', 
															'original_inventory'])
				->where([['order_id', $order->id],['status','new']])->get();

			foreach ($new_product_variatins_ids as $pv) {

				$stock = StockLevel::where([['inventory_id', $pv->to_inventory], 
											['product_variation_id', $pv->product_variation_id]])
					->first();

				$stock->update([
					//'current_stock_level' => $stock->current_stock_level - $pv->quantity,
					'on_hold' => $stock->on_hold - $pv->quantity,
					'sold_quantity' => $stock->sold_quantity + $pv->quantity
				]);
			}
		}
		return ['order' => $order, 'gift_card' => $gift_card ?? null, 'covered_by_gift_card' => $covered_by_gift_card];
	}


	public function storeShippingInfo($shipping_info_data, $order_id, $address, $city_id, $city)
	{
		$shipping_info_data['order_id'] = $order_id;
		if(isset($shipping_info_data['express'])){
			if($shipping_info_data['express'] == true){
				$shipping_info_data['date'] = Carbon::now()->format('Y-m-d');
				$shipping_info_data['time'] = "مساء من 4 حتى 10 ";
			}
		}
		if(isset($shipping_info_data['date']) && $shipping_info_data['date'] < now()){
			$shipping_info_data['date'] = Carbon::now()->format('Y-m-d');
			$shipping_info_data['time'] = "مساء من 4 حتى 10 ";
		}
		//else{
		//     $date = now()->add(new DateInterval('P2D'))->format('Y-m-d');
		// }
		$shipment = Shipment::create([
			'order_id' => $shipping_info_data['order_id'],
			'type' => $shipping_info_data['type'] ?? null,
			'city_id' => $city_id,
			'city' => $city->name,
			'street' => optional($address)->street ?? $shipping_info_data['street'],
			'date' => Carbon::parse($shipping_info_data['date']) ?? null,
			'express' => $shipping_info_data['express'] ?? false,
			'time' => $shipping_info_data['time'] ?? null,
			'neighborhood' => optional($address)->neighborhood ?? $shipping_info_data['neighborhood'] ?? null,
			'lat' => $shipping_info_data['lat'] ?? null,
			'long' => $shipping_info_data['long'] ?? null,
			'receiver_first_name' => $shipping_info_data['receiver_first_name'] ?? null,
			'receiver_father_name' => $shipping_info_data['receiver_father_name'] ?? null,
			'receiver_last_name' => $shipping_info_data['receiver_last_name'] ?? null,
			'receiver_phone' => $shipping_info_data['receiver_phone'] ?? null,
			'receiver_phone2' => $shipping_info_data['receiver_phone2'] ?? null,
			'additional_details' => optional($address)->another_details ?? $shipping_info_data['additional_details'] ?? false,
		]);

		return $shipment;
	}


	public function updateOrder(array $data, $order_id): Order
	{
		$Order = Order::find($order_id);

		if (!$Order) {
			throw new NotFoundHttpException('There Is No Order Available');
		}

		$Order->update($data);

		return $Order;
	}

	public function showDash($order_id)
	{
		try {
			$order = Order::findOrFail($order_id)->load(
				[
					'address',
					'user:id,first_name,last_name,address,phone',
					'employee',
				]
			);

			return $order;
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}

	public function showOpenOrderItems($order_id, $employee){
		try {
			//     $employee_account = $employee->account;
			// if (!$employee_account){
			//     throw new Exception('Employee does not have any account');
			// }
			// $employee_role = $employee_account->roles->first();
			// if (!$employee_role){
			//     throw new Exception('Employee does not have any role');    
			// }
			// if($employee_role->name != 'warehouse_admin' && $employee_role->name != 'warehouse_manager'){
			//     throw new Exception('You do not have the permission',403);    
			// }
			if($employee->hasRole(Roles::WAREHOUSE_ADMIN)||$employee->hasRole(Roles::WAREHOUSE_MANAGER)||$employee->hasRole(Roles::DELIVERY_ADMIN)||$employee->hasRole(Roles::MAIN_ADMIN)){
				$order = Order::findOrFail($order_id)->load(
					'order_items'
				);
				$inventory_id = $employee->inventory_id;
				if($employee->hasRole(Roles::WAREHOUSE_MANAGER) && $order->inventory_id != $inventory_id){
					throw new Exception('This order belong to other inventory');
				}
				$order_items = $order->order_items()
					->select('id', 'order_id', 'to_inventory', 'product_variation_id','status', 'quantity','original_price', 'price','on_hold')
					->with([
						'product_variation:id,product_id,sku_code,color_id,size_id',
						'product_variation.product:id,name',
						'product_variation.product.main_photos:id,product_id,thumbnail,main_photo',
						'product_variation.product.pricing:id,product_id,value,currency',
						'product_variation.color',
						'product_variation.size'
					])
					->get();
				$in_stock = [];
				$show_in_stock = [];
				//if($order->status != 'processing' || $order->inventory_id != $inventory_id){
				if($order->employee_id != null || $order->inventory_id != $inventory_id){	
					$show_status = false;
				}else{
					$show_status = true;
				}

				$ready_to_deliver = true;
				$ready_to_assign = true;
				$is_assigned = true;
				if($order->packed_date == null){
					$ready_to_assign = false;    
				}
				if($order->delivery_date == null){
					$is_assigned = false;
				}
				foreach($order_items as $order_item){ 
					if($show_status == true){
						if(($order->delivery_type == 'replacing' || $order->delivery_type == 'return') && $order_item->status != 'new'){
							array_push($show_in_stock, false);        
						}else{
							array_push($show_in_stock, true);
						}
					}else{
						array_push($show_in_stock, false);    
					}  
					if($order_item->to_inventory == $inventory_id && $order_item->on_hold == 0){
						array_push($in_stock,'in stock');
						// $in_stock + ['in stock'];
					}
					elseif($order_item->to_inventory == $inventory_id && $order_item->on_hold == 1){
						array_push($in_stock,'confirm delivered');
						$ready_to_deliver = false;
						// $in_stock + ['confirm delivered'];
					}
					else{
						array_push($in_stock,'out of stock');
						$ready_to_deliver = false;
						// $in_stock + ['will be send soon'];
					}

				}
				$status = $order->delivery_type;
				$shipment_type = $order->shipment->type ?? null;
				$order_items = $order_items->map(function ($item, $key) use ($in_stock, $show_in_stock) {
					if($in_stock[$key] == 'out of stock'){
						$item->from = Inventory::where('id',$item->to_inventory)->first()->name;	
					}else{
						$item->from = null;	
					}
					$item->quantity_price = $item->price;
					$item->in_stock = $in_stock[$key];
					$item->show_in_stock = $show_in_stock[$key];
					return $item;
				});

				$status = $order->delivery_type;
				// return $order_items;
				return ['show_status'=>$show_status,
						'exchange_status'=>$status,
						'shipment_type'=>$shipment_type,
						'ready_to_deliver'=>$ready_to_deliver,
						'is_assigned'=>$is_assigned,
						'ready_to_assign'=>$ready_to_assign,
						'order_items'=>$order_items
					   ];
			}

		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}

	public function showSubOrderItems($order_id, $employee)
	{
		try {
			if ($employee->hasRole(Roles::WAREHOUSE_ADMIN) || $employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
				$order = Order::findOrFail($order_id)->load(
					'order_items'
				);
				$inventory_id = $employee->inventory_id;
				$order_items = OrderItem::where([['order_id', $order_id], ['to_inventory', $inventory_id], ['on_hold', 1]])
					->with([
						'product_variation:id,product_id,sku_code,color_id,size_id',
						'product_variation.product:id,name',
						'product_variation.product.main_photos:id,product_id,thumbnail,main_photo',
						'product_variation.product.pricing:id,product_id,value,currency',
						'product_variation.color',
						'product_variation.size'
					])->get();
				$target = Inventory::where('id',$order->inventory_id)->first()->name;
				$order_items = $order_items->map(function ($item, $key){
					$item->quantity_price = $item->price;
					return $item;
				});
				return ['order_items' => $order_items, 'target'=> $target];
			}

		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}

	public function readyToDeliver($order_id, $employee)
	{
		try {
			$ready = true;
			$locale = app()->getLocale();

			if ($employee->hasRole(Roles::WAREHOUSE_ADMIN) || $employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
				$order = Order::findOrFail($order_id)->load(
					'order_items'
				);
				$user = $order->user()->first();
				$fcm_tokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();
				$inventory_id = $employee->inventory_id;
				$order_items = $order->order_items()->get();
				// $in_stock = [];
				foreach ($order_items as $order_item) {
					if ($order_item->on_hold == 1) {
						$ready = false;
					}
				}
				if ($ready) {
					$order->update([
						'packed_date' => now()
					]);
					// $title = ['ar'=>'تم تجهيز الطلب',
					// 'en'=>'Your order was packed'];
					// $body = ['ar'=>'تم تجهيز طلبك الآن، سيتم إعلامك عند بدء التوصيل',
					// 'en'=>'Your order was packed, We will notify you when the delivery start'];

					// foreach ($fcm_tokens as $fcm) {
					// 	$fcm_token = FcmToken::where([['fcm_token', $fcm],['user_id',$user->id]])->first();
					// 	if($fcm_token->lang == 'en'){
					// 		$this->send_notification($fcm, 
					// 								 'Your order was packed',
					// 								 'Your order was packed, We will notify you when the delivery start', 
					// 								 'Order', 
					// 								 'flutter_app'); // key source	
					// 	}else{
					// 		$this->send_notification($fcm, 
					// 								 'تم تجهيز الطلب',
					// 								 'تم تجهيز طلبك الآن، سيتم إعلامك عند بدء التوصيل',
					// 								 'Order', 
					// 								 'flutter_app'); // key source
					// 	}	
					// }
					// $user->notifications()->create([
					//     'user_id' => $user->id,
					//     'type' => 'Order', // 1 is to redirect to the orders page
					//     'title' => $title,
					//     'body' => $body
					// ]);
				} else {
					throw new Exception('Please try again later, some of the products are not available');
				}
			}
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}


	public function sendSubOrder($order_id, $employee)
	{
		try {
			if ($employee->hasRole(Roles::WAREHOUSE_ADMIN) || $employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
				$inventory_id = $employee->inventory_id;
				$order = Order::findOrFail($order_id);
				$order_items = OrderItem::where([['order_id', $order_id], ['to_inventory', $inventory_id], ['on_hold', 1]])->get();
				// if($order_item->on_hold == 0){
				//     throw new Exception('item already in stock');
				// }
				foreach ($order_items as $order_item) {
					$order_item->update([
						'to_inventory' => $order->inventory_id,
					]);
				}
				$order->update(['status' => 'in_delivery']);
				return $order;
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function confirmReceiveSub($order_item_id, $employee)
	{
		try {
			if ($employee->hasRole(Roles::WAREHOUSE_ADMIN) || $employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
				$inventory_id = $employee->inventory_id;
				$order_item = OrderItem::findOrFail($order_item_id);
				if ($order_item->on_hold == 0) {
					throw new Exception('item already in stock');
				}
				if ($order_item->to_inventory == $inventory_id && $order_item->on_hold == 1) {
					$order_item->update([
						'on_hold' => 0,
					]);
				}
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function cashOnDelivery($data)
	{
		try{
			DB::beginTransaction();
			$order_id = $data['order_ref'];
			$order = Order::where('id', $order_id)->first();
			$inventory_id = $order->inventory_id;
			//$product_variatins_ids = OrderItem::select(['id', 'product_variation_id', 'quantity', 'to_inventory'])->where('order_id', $order_id)->get();
			$new_product_variatins_ids = OrderItem::select(['id', 'product_variation_id', 'quantity', 'to_inventory', 'original_inventory'])->where([['order_id', $order_id],['status','new']])->get();
			$return_product_variatins_ids = OrderItem::select(['id', 'product_variation_id', 'quantity', 'to_inventory', 'original_inventory'])->where([['order_id', $order_id],['status','return']])->get();
			/*
		$order_items = $order->order_items()
				->where('status','new')->get();
			foreach($order_items as $order_item){
				$stock_level = StockLevel::where('inventory_id',$order_item->to_inventory)->first();
				$stock_level->update([
					'current_stock_level' => $stock_level->current_stock_level + $order_item->quantity
				]);
				$order_item->update([
					'status' => 'cancelled'
				]);
				//$order_item->delete();
			}
		*/
			foreach ($new_product_variatins_ids as $pv) {
				$stock = StockLevel::where([['inventory_id', $pv->to_inventory], ['product_variation_id', $pv->product_variation_id]])->first();
				$stock->update([
					//'current_stock_level' => $stock->current_stock_level - $pv->quantity,
					'on_hold' => $stock->on_hold - $pv->quantity,
					'sold_quantity' => $stock->sold_quantity + $pv->quantity
				]);
			}

			foreach ($return_product_variatins_ids as $pv) {
				$stock = StockLevel::where([['inventory_id', $pv->to_inventory], ['product_variation_id', $pv->product_variation_id]])->first();
				if(!$stock){
					$stock = StockLevel::create([
						'product_variation_id' => $pv->product_variation_id,
						'inventory_id' => $pv->to_inventory,
						'name' => Str::random(5),
						'min_stock_level' => 3,
						'max_stock_level' => 1000,
						'target_date' => now(),
						'sold_quantity' => 0,
						'status' => 'slow-movement',
						'current_stock_level' => 0
					]);	
				}
				$stock->update([
					'current_stock_level' => $stock->current_stock_level + $pv->quantity,
					//'on_hold' => $stock->on_hold + $pv->quantity,
					//'sold_quantity' => $stock->sold_quantity - $pv->quantity
				]);

				$original_stock = StockLevel::where([['inventory_id', $pv->original_inventory], ['product_variation_id', $pv->product_variation_id]])->first();
				$original_stock->update([
					//'current_stock_level' => $original_stock->current_stock_level + $pv->quantity,
					//'on_hold' => $stock->on_hold + $pv->quantity,
					'sold_quantity' => $original_stock->sold_quantity - $pv->quantity
				]);
			}

			$employee = Employee::where('inventory_id',$order->inventory_id)->whereHas('account', function ($query) {
				$query->whereHas('roles', function ($query) {
					$query->where('name','warehouse_manager');	
				});
			})->first();

			if($employee){				
				$title = ["ar"=>"تم إنشاء طلب شراء جديد",
						  "en"=>"A new order was created"];

				$body = ["ar"=>"تم إنشاء طلب شراء جديد",
						 "en"=>"A new order was created"];

				$fcm_tokens = $employee->fcm_tokens()->pluck('fcm_token')->toArray();

				foreach($fcm_tokens as $fcm){
					$fcm_token = FcmToken::where([['fcm_token', $fcm],['employee_id',$employee->id]])->first();
					if($fcm_token->lang == 'en'){
						$this->send_notification($fcm, 
												 'A new order was created',
												 'A new order was created', 
												 'dashboard_orders', 
												 'dashboard'); // key source	
					}else{
						$this->send_notification($fcm, 
												 'تم إنشاء طلب شراء جديد',
												 'تم إنشاء طلب شراء جديد',
												 'dashboard_orders', 
												 'dashboard'); // key source
					}	
				}


				$employee->notifications()->create([
					'employee_id'=>$employee->id,
					'type'=> "dashboard_orders", // 1 is to redirect to the orders page
					'title'=>$title,
					'body'=>$body
				]);	
			}
			DB::commit();
			//return "Order was canceled successfully";	
		}catch(Exception $th){
			DB::rollBack();
			throw new Exception($th->getMessage());
		}
		//foreach ($product_variatins_ids as $pv) {
		//    $stock = StockLevel::where([['inventory_id', $pv->to_inventory], ['product_variation_id', $pv->product_variation_id]])->first();
		//    $stock->update([
		//        'on_hold' => $stock->on_hold - $pv->quantity,
		//        'sold_quantity' => $stock->sold_quantity + $pv->quantity
		//    ]);
		//}
	}


	public function showOrderDetails($order_id, $employee)
	{
		try {
			$order = Order::findOrFail($order_id);
			$inventory_id = $employee->inventory_id;

			if ($employee->hasRole(Roles::WAREHOUSE_MANAGER) && $order->inventory_id != $inventory_id) {
				throw new Exception('This order belongs to another inventory');
			}

			$order = Order::select('id', 'employee_id','original_order_id', 'inventory_id', 'user_id', 'invoice_number', 'payment_method', 'total_price', 'discounted_by_coupon', 'total_quantity', 'shipping_fee', 'status', 'is_gift', 'gift_message', 'packed_date', 'created_at', 'shipping_date', 'receiving_date')
				->with([
					'user:id,first_name,last_name,phone',
					'employee:id,first_name,last_name,phone,shift_id',
					'employee.shift',
					'shipment:id,receiver_first_name,receiver_father_name,receiver_last_name,receiver_phone,order_id,type,time,date,city,street,neighborhood,express',
				])
				->where('id', $order_id)
				->get();
			$orderStatus = Order::findOrFail($order_id)->status;
			$isCanceled = $orderStatus == 'canceled'? true: false;
			// Transform payment_method for each order
			/*foreach ($order as &$item) {
				$item->payment_method = $item->getPaymentMethodAttribute($item->payment_method);
			}*/ // use ->get() instead of ->findOrFail()
			// use the ->transform() method to modify the JSON structure
			return $order->transform(function ($order) use($isCanceled){
				// create a new status object with the date attributes
				$order->total_price = $order->total_price - $order->discounted_by_coupon;
				$order->status = [
					'ordered' => (\DateTime::createFromFormat('Y-m-d H:i:s', $order->created_at))->format('Y-m-d H:i:s'),
					'packed' => $order->packed_date,
					'shipping' => $order->shipping_date,
					'receiving' => $order->receiving_date,
				];
				$order->isCanceled = $isCanceled;
				$order->discounted_price = $order->total_price - $order->discounted_by_coupon;
				if(isset($order->transaction)){
					$order->transaction_source = $order->transaction->transaction_source;
				}else{
					$order->transaction_source = null;	
				}

				// unset the original date attributes
				unset ($order->packed_date, $order->created_at, $order->shipping_date, $order->receiving_date);
				// return the modified order object
				return $order;
			});
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}

	public function getInvoice($order_id =null, $invoice_number = null)
	{
		try {
			if($order_id != null){
				$order = Order::findOrFail($order_id);
			}
			elseif($invoice_number != null){
				$order = Order::where('invoice_number', $invoice_number)->firstOrFail();	
			}
			if($order->replacedOrReturned == 1){
				$order_id = Order::where('invoice_number',$order->invoice_number)->latest()->first()->id;
			}
			// return Order::find($order_id)>select(['id','invoice_number','total_price','paid_by_user','created_at'])
			$order = Order::select(['id', 'original_order_id','invoice_number','total_quantity', 'payment_method', 'total_price', 'paid_by_user','discounted_by_coupon','price_without_offers','delivery_type','covered_by_gift_card','gift_id', 'shipping_fee', 'total_quantity', 'created_at'])
				->where('id', $order_id)
				->firstOrFail()
				->load([
					'order_items:id,product_variation_id,order_id,quantity,original_price,price,status',
					'order_items.product_variation:id,product_id', // Load the product variation
					'order_items.product_variation.product:id,name', // Load the product through the product variation
					'shipment:id,order_id,type,receiver_first_name,date,time,receiver_last_name,receiver_father_name,city,street,neighborhood',
					'invoice:id,order_id,gift_card_balance,coupon_percenage'
				]);
			$order->discounted_price = $order->total_price - $order->discounted_by_coupon - $order->covered_by_gift_card;
			if($order->original_order_id != null){
				$percentage = 0;
				$original_order = Order::findOrFail($order->original_order_id);
				$cpn = Coupon::find($original_order->coupon_id);
				if($cpn){
					$percentage = $cpn->percentage;
				}
				$order->old_items_price =  ($order->returnOrderPrice - ((($order->returnOrderPrice)*$percentage)/100));
			}
			else{
				$order->old_items_price =  $order->returnOrderPrice;	
			}
			$order->new_items_price =  $order->newItemsPrice;
			//$order->discounted_price = $order->total_price - $order->discounted_by_coupon;
			if(isset($order->transaction_source)){
				$order->transaction_source = $order->transaction->transaction_source;	
			}else{
				$order->transaction_source = null;
			}
			return $order;
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}



	public function showOrderItems($order_id)
	{
		try {
			$order = Order::findOrFail($order_id)->load(
				'order_items'
			);

			$order_items = $order->order_items()
				->select('id', 'order_id', 'product_variation_id', 'quantity', 'price')
				->with([
					'product_variation:id,product_id,sku_code',
					'product_variation.product:id,name',
					'product_variation.product.main_photos:id,product_id,thumbnail,main_photo',
					'product_variation.product.pricing:id,product_id,value,currency',
				])
				->paginate(8);

			return $order_items;
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}

	public function show($Order_id): Order
	{
		$Order = Order::findOrFail($Order_id);

		return $Order;
	}

	public function calculatePrice($order_items_data)
	{
		$total_price = 0;
		$total_quantity = 0;

		// Group the order items data by product variation ID
		$product_variations_data = Collection::make($order_items_data)->groupBy('product_variation_id');


		// Extract the product variation IDs and product IDs as separate arrays
		$product_variation_ids = array_column($order_items_data, 'product_variation_id');

		$grouped_product_variations = ProductVariation::whereIn('id', $product_variation_ids)
			->orderByRaw("FIELD(id, " . implode(',', $product_variation_ids) . ")")
			->with([
				'product',
				'product.pricing:id,product_id,currency,value,location',
				'product.discount:id,percentage,start_date,end_date',
				'group',
				'stock_levels',
			])
			->get()
			->groupBy(function ($item) {

				if ($item->group && $item->group->valid == 1 && (Str::lower($item->group->promotion_type) == 'bogo' || Str::lower($item->group->promotion_type) == 'bogh' || Str::lower($item->group->promotion_type) == 'btgo' || Str::lower($item->group->type) == 'discount')) {
					// Retrieve the first offer associated with the group and get its type
					// return Str::lower($item->group->offers()->first()->type);
					if($item->group->promotion_type == 'flash_sales'){
						return Str::lower('discount');
					}
					return Str::lower($item->group->promotion_type);


				} else {



					// If the product variation does not belong to a group, assign it to 'no_group'
					return 'no_group';
				}
			});
		// return $grouped_product_variations;
		// Iterate over the grouped product variations and process each group
		foreach ($grouped_product_variations as $key => $product_variations) {

			// Generate the method name based on the group key
			$method = 'calculate_price' . Str::studly(str_replace('_', '', Str::upper($key)));
			switch ($key) {
				case 'bogo':
					// Calculate the BOGO (Buy One Get One) offer
					if (method_exists($this, $method)) {
						$order_items_data = $this->calculate_priceBOGO($product_variations, $product_variations_data);

						$total_price += $order_items_data['price'];
						$total_quantity += $order_items_data['quantity'];
					}

					break;

				case 'btgo':
					// Calculate the BTGO (Buy Two Get One) offer
					if (method_exists($this, $method)) {
						$order_items_data = $this->calculate_priceBTGO($product_variations, $product_variations_data);
						$total_price += $order_items_data['price'];
						$total_quantity += $order_items_data['quantity'];
					}

					break;

				case 'bogh':
					// Calculate the BOGH (Buy One Get Half) offer
					if (method_exists($this, $method)) {
						$order_items_data = $this->calculate_priceBOGH($product_variations, $product_variations_data);
						$total_price += $order_items_data['price'];
						$total_quantity += $order_items_data['quantity'];
					}

					break;

				case 'discount':
					if (method_exists($this, $method)) {
						$order_items_data = $this->calculate_priceDiscount($product_variations, $product_variations_data);
						$order_items_data;
						$total_price += $order_items_data['price'];
						$total_quantity += $order_items_data['quantity'];
					}

					break;

				case 'pair':
					// Calculate the pair offer
					// $this->calculatePair();
					break;

				case 'no_group':
					// Calculate the offer for product variations without a group
					if (method_exists($this, $method)) {
						$order_items_data = $this->calculate_priceNOGROUP($product_variations, $product_variations_data);
						$order_items_data;
						$total_price += $order_items_data['price'];
						$total_quantity += $order_items_data['quantity'];
					}

					break;

				default:
					// Default case if no specific offer type is matched
					break;
			}
		}
		return [
			'total_price' => $total_price,
			'covered_by_gift' => null,
			'discounted_by_coupon' => null,
			'price_after_coupon_and_gift' => $total_price,
			'total_quantity' => $total_quantity
		];
		// Return the created order items


	}

	public function calculate_priceBOGO($product_variations, $product_variations_data)
	{
		$total_quantity = 0;
		$total = 0;
		$product_variations = collect($product_variations)
			->sortBy(function ($product_variation) {
				return $product_variation['product']['pricing']['value'];
			}) // Sort product variations by the pricing value of the associated product
			->flatten()->reverse(); // Flatten the collection

		$original_prices = [];
		$prices = [];
		$pvs = []; //array for product variations
		$gids = []; //array for group IDs

		// Iterate over the product variations
		foreach ($product_variations as $product_variation) {
			$quantity = (int) $product_variations_data[$product_variation->id]->first()['quantity']; // Get the quantity for the current product variation
			$total_quantity += $quantity;
			$product = $product_variation->product; // Get the associated product
			$pricing = $product->pricing; // Get the pricing value for the associated product
			for ($i = 0; $i < $quantity; $i++) {
				array_push($pvs, $product_variation->id);
				array_push($gids, $product_variation->group_id);
				array_push($original_prices, $pricing->value);
			}

			// $photo = $product->main_photos()->first()->path;
		}
		$gids_unique = array_unique($gids); // get unique gids
		$pvs_new = [];
		$original_prices_new = [];

		foreach ($gids_unique as $gid) {
			$pvs_new[$gid] = array_values(array_filter($pvs, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));

			$original_prices_new[$gid] = array_values(array_filter($original_prices, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));
		}
		foreach ($original_prices_new as $gid => $original_prices_gid) {
			$prices[$gid] = array_map(function ($value, $index) {
				if ($index % 2 == 1) {
					return 0;
				}
				return $value;
			}, $original_prices_gid, array_keys($original_prices_gid));
		}
		foreach ($pvs_new as $key => $pvs) {
			$grouped = array_count_values($pvs);
			foreach ($grouped as $pv => $quantity) {
				$indices = array_keys($pvs, $pv);
				$price = array_sum(array_intersect_key($prices[$key], array_flip($indices)));
				$total += $price;
			}
		}
		return [
			'price' => $total,
			'quantity' => $total_quantity
		];
	}

	public function calculate_priceBTGO($product_variations, $product_variations_data)
	{
		$total_qty = 0;
		$total = 0;
		$product_variations = collect($product_variations)
			->sortBy(function ($product_variation) {
				return $product_variation['product']['pricing']['value'];
			}) // Sort product variations by the pricing value of the associated product
			->flatten()->reverse(); // Flatten the collection

		// $quantities = collect($product_variations_data)
		//     ->flatten(1)
		//     ->pluck('quantity');

		// // Calculate the total quantity using the 'sum' method
		// $total_quantity = $quantities->sum();
		$ids = $product_variations->pluck('id');
		$total_quantity = collect($product_variations_data)
			->flatten(1) // Flatten to combine all nested arrays
			->whereIn('product_variation_id', $ids) // Filter only matching IDs
			->sum('quantity');
		$limit = floor($total_quantity / 3);
		$original_prices = [];
		$prices = [];
		$pvs = [];
		$gids = [];
		foreach ($product_variations as $product_variation) {
			$quantity = (int) $product_variations_data[$product_variation->id]->first()['quantity']; // Get the quantity for the current product variation
			$total_qty += $quantity;
			$total_price = 0; // Initialize the total price as 0
			$product = $product_variation->product; // Get the associated product
			$pricing = $product->pricing; // Get the pricing value for the associated product
			for ($i = 1; $i < $quantity + 1; $i++) {
				array_push($pvs, $product_variation->id);
				array_push($gids, $product_variation->group_id);
				// If the current length of the prices array is even, push the pricing value, else push 0
				// array_push($prices, $i % 3 == 0 ? 0 : $pricing->value);                
				array_push($original_prices, $pricing->value);
			}

			if ($limit > $quantity) {
				$applied_quantity = $limit - $quantity;
				$total_price = 0; // Set the total price as 0 since the BOGO offer is fully applied
				$limit -= $quantity;
			} elseif ($limit <= $quantity && $limit > 0) {
				$applied_quantity = $quantity - $limit;
				$total_price = $pricing->value * $applied_quantity; // Calculate the total price based on the applied quantity and pricing value
			}

			$productVariationData = $product_variations_data[$product_variation->id]->first();
			// return $productVariationData;
			$productVariationData['price'] = $total_price;
			$productVariationData['currency'] = $pricing->currency;

			$priced_product_variations[] = $productVariationData; // Add the priced product variation data to the result array
			$total_price = 0; // Reset the total price to 0 for the next iteration
		}

		$gids_unique = array_unique($gids); // get unique gids
		$pvs_new = [];
		$original_prices_new = [];

		foreach ($gids_unique as $gid) {
			$pvs_new[$gid] = array_values(array_filter($pvs, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));

			$original_prices_new[$gid] = array_values(array_filter($original_prices, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));
		}
		foreach ($original_prices_new as $gid => $original_prices_gid) {
			$prices[$gid] = array_map(function ($value, $index) {
				if ($index % 3 == 2) {
					return 0;
				}
				return $value;
			}, $original_prices_gid, array_keys($original_prices_gid));
		}
		foreach ($pvs_new as $key => $pvs) {
			$grouped = array_count_values($pvs);
			foreach ($grouped as $pv => $quantity) {
				$indices = array_keys($pvs, $pv);
				$price = array_sum(array_intersect_key($prices[$key], array_flip($indices)));
				$total += $price;
			}
		}
		return [
			'price' => $total,
			'quantity' => $total_qty
		];
	}

	public function calculate_priceBOGH($product_variations, $product_variations_data)
	{
		$total = 0;
		$total_qty = 0;
		$product_variations = collect($product_variations)
			->sortBy(function ($product_variation) {
				return $product_variation['product']['pricing']['value'];
			}) // Sort product variations by the pricing value of the associated product
			->flatten()->reverse(); // Flatten the collection
		// $quantities = collect($product_variations_data)
		//     ->flatten(1)
		//     ->pluck('quantity');

		// // Calculate the total quantity using the 'sum' method
		// $total_quantity = $quantities->sum();
		$ids = $product_variations->pluck('id');
		$total_quantity = collect($product_variations_data)
			->flatten(1) // Flatten to combine all nested arrays
			->whereIn('product_variation_id', $ids) // Filter only matching IDs
			->sum('quantity');
		$limit = floor($total_quantity / 2);
		$original_prices = [];
		$prices = [];
		$pvs = [];
		$gids = [];
		foreach ($product_variations as $product_variation) {
			$quantity = (int) $product_variations_data[$product_variation->id]->first()['quantity']; // Get the quantity for the current product variation
			$total_price = 0; // Initialize the total price as 0
			$total_qty += $quantity;
			$product = $product_variation->product; // Get the associated product
			$pricing = $product->pricing; // Get the pricing value for the associated product
			for ($i = 1; $i < $quantity + 1; $i++) {
				array_push($pvs, $product_variation->id);
				array_push($gids, $product_variation->group_id);
				array_push($original_prices, $pricing->value);
			}

			if ($limit > $quantity) {
				$applied_quantity = $limit - $quantity;
				$total_price = 0; // Set the total price as 0 since the BOGO offer is fully applied
				$limit -= $quantity;
			} elseif ($limit <= $quantity && $limit > 0) {
				$applied_quantity = $quantity - $limit;
				$total_price = $pricing->value * $applied_quantity; // Calculate the total price based on the applied quantity and pricing value
			}

			$productVariationData = $product_variations_data[$product_variation->id]->first();
			// return $productVariationData;
			$productVariationData['price'] = $total_price;
			$productVariationData['currency'] = $pricing->currency;

			$priced_product_variations[] = $productVariationData; // Add the priced product variation data to the result array
			$total_price = 0; // Reset the total price to 0 for the next iteration
		}
		$gids_unique = array_unique($gids); // get unique gids

		$pvs_new = [];
		$original_prices_new = [];

		foreach ($gids_unique as $gid) {
			$pvs_new[$gid] = array_values(array_filter($pvs, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));

			$original_prices_new[$gid] = array_values(array_filter($original_prices, function ($k) use ($gid, $gids) {
				return $gids[$k] == $gid;
			}, ARRAY_FILTER_USE_KEY));
		}
		foreach ($original_prices_new as $gid => $original_prices_gid) {
			$prices[$gid] = array_map(function ($value, $index) {
				if ($index % 2 == 1) {
					return $value / 2;
				}
				return $value;
			}, $original_prices_gid, array_keys($original_prices_gid));
		}
		foreach ($pvs_new as $key => $pvs) {
			$grouped = array_count_values($pvs);
			foreach ($grouped as $pv => $quantity) {
				$indices = array_keys($pvs, $pv);
				$price = array_sum(array_intersect_key($prices[$key], array_flip($indices)));
				$total += $price;
			}
		}
		return [
			'price' => $total,
			'quantity' => $total_qty
		];
	}

	public function calculate_priceDiscount($product_variations, $product_variations_data)
	{
		$total = 0;
		$total_quantity = 0;
		foreach ($product_variations as $product_variation) {
			$group_id = $product_variation->group_id;
			$percentage = $product_variation->group->percentage;
			$price = ceil((($product_variation->product->pricing->value) * (100 - $percentage)) / 100);
			$quantity = $product_variations_data[$product_variation->id]->first()['quantity'];
			$total += ($price * $quantity);
			$total_quantity += $quantity;

		}

		return [
			'price' => $total,
			'quantity' => $total_quantity
		];

	}

	// Calculate No Group Offer
	public function calculate_priceNOGROUP($product_variations, $product_variations_data)
	{
		$total = 0;
		$total_quantity = 0;

		foreach ($product_variations as $product_variation) {
			$price = $product_variation->product->pricing->value;
			$quantity = $product_variations_data[$product_variation->id]->first()['quantity'];
			$product = $product_variation->product;
			$quantity = $product_variations_data[$product_variation->id]->first()['quantity'];
			$total += ($price * $quantity);
			$total_quantity += $quantity;

		}

		return [
			'price' => $total,
			'quantity' => $total_quantity
		];
	}

	public function calculateDiscounted($response, $code, $gift_code)
	{
		$total_price = $response['total_price'];
		$total_quantity = $response['total_quantity'];
		$discounted_by_coupon = 0;
		$covered_by_gift_card = 0;
		$paid_by_user = $total_price;
		$coupon = Coupon::where([['code', $code], ['type', 'coupon']])->first();
		if ($coupon) {
			$percentage = $coupon->percentage;
			$discounted_by_coupon = ($total_price * $percentage) / 100;
			$paid_by_user = $total_price - $discounted_by_coupon;
		}

		$gift_card = Coupon::where([['code', $gift_code], ['type', 'gift']])->first();

		if ($gift_card) {
			if ($gift_card->amount_off >= $paid_by_user) {
				$covered_by_gift_card = $paid_by_user;
				$paid_by_user = 0;
			} else {
				$covered_by_gift_card = $gift_card->amount_off;
				$paid_by_user = $paid_by_user - $covered_by_gift_card;
			}
		}
		return [
			'total_price' => $total_price,
			'covered_by_gift' => $covered_by_gift_card,
			'discounted_by_coupon' => $discounted_by_coupon,
			'price_after_coupon_and_gift' => $paid_by_user,
			'total_quantity' => $total_quantity
		];
		return ['order' => $order, 'gift_card' => $gift_card ?? null, 'covered_by_gift_card' => $covered_by_gift_card];

		// first apply coupon
		// second apply gift card
		// add column to order to determine how much of total price was paid using the gift card
		$total_price = $response['total_price'];
		$total_quantity = $response['total_quantity'];
		$discounted_by_coupon = 0;
		$paid_by_user = $total_price;
		$coupon = Coupon::where('code', $code)->first();
		if ($coupon) {
			$percentage = $coupon->percentage;
			$discounted_by_coupon = ($total_price * $percentage) / 100;
			$paid_by_user = $total_price - $discounted_by_coupon;
		}


	}

	public function changeOnHoldToSold($items){
		foreach ($items as $pv) {
			$stock = StockLevel::where([['inventory_id', $pv->to_inventory], ['product_variation_id', $pv->product_variation_id]])->first();
			$stock->update([
				//'current_stock_level' => $stock->current_stock_level - $pv->quantity,
				'on_hold' => $stock->on_hold - $pv->quantity,
				'sold_quantity' => $stock->sold_quantity + $pv->quantity
			]);
		}
	}

	public function decreaseReturnedSoldItems($items){
		foreach ($items as $pv) {
			$stock = StockLevel::where([['inventory_id', $pv->to_inventory], ['product_variation_id', $pv->product_variation_id]])->first();
			if(!$stock){
				$stock = StockLevel::create([
					'product_variation_id' => $pv->product_variation_id,
					'inventory_id' => $pv->to_inventory,
					'name' => Str::random(5),
					'min_stock_level' => 3,
					'max_stock_level' => 1000,
					'target_date' => now(),
					'sold_quantity' => 0,
					'status' => 'slow-movement',
					'current_stock_level' => 0
				]);	
			}
			$stock->update([
				'current_stock_level' => $stock->current_stock_level + $pv->quantity,
			]);

			$original_stock = StockLevel::where([['inventory_id', $pv->original_inventory], ['product_variation_id', $pv->product_variation_id]])->first();
			$original_stock->update([
				'sold_quantity' => $original_stock->sold_quantity - $pv->quantity
			]);
		}	
	}

	public function delete(int $order_id): void
	{
		$order = Order::find($order_id);

		if (!$order) {
			throw new InvalidArgumentException('Order not found');
		}

		$order->delete();
	}

	public function forceDelete(int $order_id): void
	{
		$order = Order::find($order_id);

		if (!$order) {
			throw new InvalidArgumentException('Order not found');
		}

		$order->forceDelete();
	}

	// Filters
	protected function applyFilters($query, array $filters)
	{
		$appliedFilters = [];
		foreach ($filters as $attribute => $value) {
			$column_name = Str::before($attribute, '_');
			$method = 'filterBy' . Str::studly($column_name);
			if (method_exists($this, $method) && isset ($value) && !in_array($column_name, $appliedFilters)) {
				$query = $this->{$method}($query, $filters);
				$appliedFilters[] = $column_name;
			}
		}

		return $query;
	}

	protected function filterByInvoice($query, $filter_data)
	{
		return $query->where('invoice_number', 'LIKE', '%' . $filter_data['invoice_number'] . '%');
	}

	protected function filterByQuantity($query, $filter_data)
	{
		return $query->where('total_quantity', $filter_data['quantity']);
	}

	protected function filterByStatus($query, $filter_data)
	{
		if($filter_data['status'] == 'completed'){
			return $query->whereIn('status', ['received','replaced','returned','fulfilled']);
		}
		return $query->where('status', $filter_data['status']);
	}

	protected function filterByPricing($query, $filter_data)
	{
		return $query->whereBetween('total_price', [$filter_data['pricing_min'], $filter_data['pricing_max']]);
	}

	protected function filterByDay($query, $filter_data)
	{
		return $query->whereDate('created_at', $filter_data['day']);
	}

	protected function filterByDate($query, $filter_data)
	{
		Log::debug('filter date is: '. $filter_data['date_min'] . ' and '.$filter_data['date_max']);
		$date_min = $filter_data['date_min'] ?? 0;
		$date_max = $filter_data['date_max'] ?? date('Y-m-d');

		return $query->whereBetween('created_at', [$date_min, $date_max]);
	}

	protected function filterByDelivery($query, $filter_data)
	{
		$delivery_min = $filter_data['delivery_min'] ?? 0;
		$delivery_max = $filter_data['delivery_max'] ?? date('Y-m-d');

		return $query->whereBetween('created_at', [$delivery_min, $delivery_max]);
	}

	protected function filterByInventory($query, $filter_data)
	{
		return $query->where('inventory_id', $filter_data['inventory']);
		// return $query->get();

	}
	protected function filterBySearch($query, $filter_data)
	{
		// return $query->whereLike('shipment_name', $filter_data['search']);
		$search = $filter_data['search'];
		// dd($search);
		return $query->where(function ($query) use ($search) {
			$query->where('invoice_number', 'LIKE', '%' . $search . '%')
				->orWhereHas('user', function ($query) use ($search) {
					$query->where(function ($query) use ($search) {
						$query->where('first_name', 'LIKE', '%' . $search . '%')
							->orWhere('last_name', 'LIKE', '%' . $search . '%')
							->orWhere('phone', 'LIKE', '%' . $search . '%');
					});
				});
		});
	}
	// Sort
	protected function applySort($query, array $sort_data)
	{
		return $query->orderBy($sort_data['sort_key'], $sort_data['sort_value']);
	}




	public function success()
	{
		// method to redirect user to success url
		// update payment_method_types , payment_status in Transaction table
		// update paid in Order table

		$stripe = new StripeClient(config("app.STRIPE_SECRET_KEY"));
		$session = $stripe->checkout->sessions->retrieve($_GET['session_id']);
		// return $session;
		$transaction = Transaction::where('sessionId', $session->id)->first();
		$transaction->update([
			'payment_method_type' => $session->payment_method_types[0],
			'payment_status' => $session->payment_status,
		]);
		$order_id = $transaction->order_id;
		$order = Order::where('id', $order_id)->first();
		$shipment = $this->createShipments($order->shipment);
		$order->shipment->shipment_id = $shipment['Shipments'][0]['ID'];
		//    $order->shipment->label_url=$shipment['Shipments'][0]['ShipmentLabel']['LabelURL'];
		$order->shipment->save();
		$order->update([
			'paid' => 1,
			'payment_status' => $session->payment_status,
		]);
		$details = OrderDetail::select('product_id', 'quantity')->where('order_id', $order->id)->get();
		//    return $details;
		foreach ($details as $detail) {
			$product = Product::where('id', $detail['product_id'])->first();
			$product->update([
				'quantity' => $product->quantity - $detail['quantity'],
			]);
			if ($product->quantity <= 5) {
				$admins = User::where('role', 'ADM')->get();
				Notification::send($admins, new ProductNotification($product));
			}
		}

		$admins = User::where('role', 'ADM')->get();
		Notification::send($admins, new OrderNotification($order));

		//  return  response()->json(['message'=>'Transaction has been updated successfully','shipment'=>$shipment],200);
		return redirect('https://arabesquegallery.ae/api/success')->with("message", 'Transaction has been updated successfully');
	}

	// method to redirect user to cancel url
	// update  payment_status in Transaction table

	public function cancel()
	{
		$stripe = new StripeClient(config("app.STRIPE_SECRET_KEY"));
		return $session = $stripe->checkout->sessions->retrieve($_GET['session_id']);
		$transaction = Transaction::where('sessionId', $session->id)->first();
		$transaction->update([
			// 'payment_method_type'=>$session->payment_method_types[0],
			'payment_status' => $session->payment_status,
		]);
		$order_id = $transaction->order_id;
		$order = Order::where('id', $order_id)->first();
		$order->update([
			'payment_status' => $session->payment_status,
		]);


		return response()->json(['message' => 'Some Thing Error'], 200);
	}

	public function webhook()
	{
		// This is your Stripe CLI webhook secret for testing your endpoint locally.

		$endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
		$payload = @file_get_contents('php://input');
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
		$event = null;

		try {
			$event = \Stripe\Webhook::constructEvent(
				$payload,
				$sig_header,
				$endpoint_secret
			);
		} catch (\UnexpectedValueException $e) {
			// Invalid payload
			return response('', 400);
		} catch (\Stripe\Exception\SignatureVerificationException $e) {
			// Invalid signature
			return response('', 400);
		}

		// Handle the event
		// Handle the event
		switch ($event->type) {
			case 'checkout.session.async_payment_failed':
				$session = $event->data->object;
				$transaction = Transaction::where('sessionId', $session->id)->first();
				$transaction->update([
					// 'payment_method_type'=>$session->payment_method_types[0],
					'payment_status' => $session->payment_status,
				]);
				$order_id = $transaction->order_id;
				$order = Order::where('id', $order_id)->first();
				$order->update([
					'payment_status' => $session->payment_status,
				]);

			case 'checkout.session.async_payment_succeeded':
				$session = $event->data->object;
				$transaction = Transaction::where('sessionId', $session->id)->first();
				$transaction->update([
					'payment_method_type' => $session->payment_method_types[0],
					'payment_status' => $session->payment_status,
				]);
				$order_id = $transaction->order_id;
				$order = Order::where('id', $order_id)->first();
				$order->update([
					'paid' => 1,
					'payment_status' => $session->payment_status,
				]);
				$details = OrderDetail::select('product_id', 'quantity')->where('order_id', $order->id)->get();
				//    return $details;
				foreach ($details as $detail) {
					$product = Product::where('id', $detail['product_id'])->first();

					$product->update([
						'quantity' => $product->quantity - $detail['quantity'],
					]);
					if ($product->quantity <= 5) {
						$admins = User::where('role', 'ADM')->get();
						Notification::send($admins, new ProductNotification($product));
					}
				}
				$admins = User::where('role', 'ADM')->get();
				Notification::send($admins, new OrderNotification($order));

			case 'checkout.session.completed':
				$session = $event->data->object;
				$transaction = Transaction::where('sessionId', $session->id)->first();
				$transaction->update([
					'payment_method_type' => $session->payment_method_types[0],
					'payment_status' => $session->payment_status,
				]);
				$order_id = $transaction->order_id;
				$order = Order::where('id', $order_id)->first();
				$order->update([
					'paid' => 1,
					'payment_status' => $session->payment_status,
				]);
				$details = OrderDetail::select('product_id', 'quantity')->where('order_id', $order->id)->get();
				//    return $details;
				foreach ($details as $detail) {
					$product = Product::where('id', $detail['product_id'])->first();

					$product->update([
						'quantity' => $product->quantity - $detail['quantity'],
					]);
					if ($product->quantity <= 5) {
						$admins = User::where('role', 'ADM')->get();
						// Notification::send($admins, new ProductNotification($product));
					}
				}
				$admins = User::where('role', 'ADM')->get();
				// Notification::send($admins, new OrderNotification($order));

			case 'checkout.session.expired':
				$session = $event->data->object;
				// ... handle other event types
			default:
				echo 'Received unknown event type ' . $event->type;
		}
		return response('');
	}
}

