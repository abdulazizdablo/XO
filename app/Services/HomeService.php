<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use App\Models\User;
use App\Traits\DateScope;
use Carbon\Carbon;
use App\Models\Category;
use App\Models\Section;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isNull;

class HomeService
{
    use DateScope;

    public function getHomeCounts()
    {
        $inventory_id = request('inventory_id');
        // $dateScope = request('date_scope');
        $sales_dateScope = request('sales_dateScope');
        $sold_dateScope = request('sold_dateScope');
        $orders_dateScope = request('orders_dateScope');

		$orderItemsExceptThisMonth = [];
		$openOrdersExceptThisMonth = [];
		$ordersExceptThisMonth = [];
		
		$OrderItemsCounts = OrderItem::where('status',null)->count();
		$allOrdersCounts = Order::count();
		$allOpenOrdersCounts = Order::where('paid', 0)->count();

		if($orders_dateScope == 'Today' ){
			$ordersExceptThisMonth = Order::whereDate('created_at', '>', Carbon::now()->startOfDay())->count();
			$new_order_counts = Order::whereDate('created_at', '>=', Carbon::now()->startOfDay())
				->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->count();
			
			$old_order_counts = Order::whereBetween('created_at', [Carbon::now()->subDay()->startOfDay(), Carbon::now()->startOfDay()])
				->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->count();
						
		}elseif($orders_dateScope == 'last_week'){
				$new_order_counts = Order::whereBetween('created_at', [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->startOfDay()])
				->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->count();
			
				$old_order_counts = Order::whereBetween('created_at', [Carbon::now()->subDays(14)->startOfDay(), Carbon::now()->subDays(7)->startOfDay()])->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->count();
		
		}elseif($orders_dateScope == 'last_month' ){
			$new_order_counts = Order::whereDate('created_at', '>=', Carbon::now()->subMonth()->startOfDay())
				->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->count();
			$old_order_counts = Order::whereBetween('created_at', [Carbon::now()->subMonths(2)->startOfDay(), Carbon::now()->subMonth()->startOfDay()])->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->count();
			
		}elseif($orders_dateScope == 'last_year' ){
		   	
			$new_order_counts = Order::whereDate('created_at', '>=', Carbon::now()->subYear()->startOfDay())
				->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->count();
			$old_order_counts = Order::whereBetween('created_at', [Carbon::now()->subYears(2)->startOfDay(), Carbon::now()->subYear()->startOfDay()])->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->count();
		}
		
		if ($old_order_counts == 0) {
			$percentageDifferenceOpenOrders = 0;
		} else {
			$percentageDifferenceOpenOrders = (($new_order_counts - $old_order_counts) / $old_order_counts) * 100;
		}

		if($sales_dateScope == 'Today'){
			$new_sales_counts = Order::whereDate('created_at', '>', Carbon::now()->startOfDay())
				->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->sum('total_price');
						
			$old_sales_counts = Order::whereBetween('created_at', [Carbon::now()->subDay()->startOfDay(), Carbon::now()->startOfDay()])->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->sum('total_price');
						
		}elseif($sales_dateScope == 'last_week'){
			$new_sales_counts = Order::whereBetween('created_at', [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->startOfDay()])
				->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->sum('total_price');
			
			$old_sales_counts = Order::whereBetween('created_at', [Carbon::now()->subDays(14)->startOfDay(), Carbon::now()->subDays(7)->startOfDay()])->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->sum('total_price');

		}elseif($sales_dateScope == 'last_month'){
			$new_sales_counts = Order::whereDate('created_at', '>=', Carbon::now()->subMonth()->startOfDay())
				->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->sum('total_price');
			$old_sales_counts = Order::whereBetween('created_at', [Carbon::now()->subMonths(2)->startOfDay(), Carbon::now()->subMonth()->startOfDay()])->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->sum('total_price');

		}elseif($sales_dateScope == 'last_year' ){
			$new_sales_counts = Order::whereDate('created_at', '>=', Carbon::now()->subYear()->startOfDay())
				->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->sum('total_price');
			$old_sales_counts = Order::whereBetween('created_at', [Carbon::now()->subYears(2)->startOfDay(), Carbon::now()->subYear()->startOfDay()])->when($inventory_id, function ($query, $inventory_id) {
                return $query->where('inventory_id', $inventory_id);
            })->sum('total_price');
		}
		
		if ($old_sales_counts == 0) {
			$percentageDifferenceOrders = 0;
		} else {
			$percentageDifferenceOrders = (($new_sales_counts - $old_sales_counts) / $old_sales_counts) * 100;
		}
		
		if($sold_dateScope == 'Today'){
			$new_sold_items = OrderItem::whereDate('created_at', '>', Carbon::now()->startOfDay())/*->where('status',null)*/
				->when($inventory_id, function ($query, $inventory_id) {
                	return $query->whereHas('order', function ($query) use ($inventory_id) {
                		$query->where('inventory_id', $inventory_id);
            });})->count();
			
			$old_sold_items = OrderItem::whereBetween('created_at', [Carbon::now()->subDay()->startOfDay(), Carbon::now()->startOfDay()])
				/*->where('status',null)*/
				->when($inventory_id, function ($query, $inventory_id) {
                	return $query->whereHas('order', function ($query) use ($inventory_id) {
                		$query->where('inventory_id', $inventory_id);
            });})->count();
			
		}elseif($sold_dateScope == 'last_week'){
			$new_sold_items = OrderItem::whereBetween('created_at', [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->startOfDay()])
				/*->where('status',null)*/
				->when($inventory_id, function ($query, $inventory_id) {
                	return $query->whereHas('order', function ($query) use ($inventory_id) {
                		$query->where('inventory_id', $inventory_id);
            });})->count();
			
			$old_sold_items = OrderItem::whereBetween('created_at', [Carbon::now()->subDays(14)->startOfDay(), Carbon::now()->subDays(7)->startOfDay()])
				/*->where('status',null)*/
				->when($inventory_id, function ($query, $inventory_id) {
                	return $query->whereHas('order', function ($query) use ($inventory_id) {
                		$query->where('inventory_id', $inventory_id);
            });})->count();

		}elseif($sold_dateScope == 'last_month'){
			$new_sold_items = OrderItem::whereDate('created_at', '>=', Carbon::now()->subMonth()->startOfDay())
				/*->where('status',null)*/
				->when($inventory_id, function ($query, $inventory_id) {
                	return $query->whereHas('order', function ($query) use ($inventory_id) {
                		$query->where('inventory_id', $inventory_id);
            });})->count();
			$old_sold_items = OrderItem::whereBetween('created_at', [Carbon::now()->subMonths(2)->startOfDay(), Carbon::now()->subMonth()->startOfDay()])/*->where('status',null)*/
				->when($inventory_id, function ($query, $inventory_id) {
                	return $query->whereHas('order', function ($query) use ($inventory_id) {
                		$query->where('inventory_id', $inventory_id);
            });})->count();

		}elseif($sold_dateScope == 'last_year' ){
			$new_sold_items = OrderItem::whereDate('created_at', '>=', Carbon::now()->subYear()->startOfDay())
				/*->where('status',null)*/
				->when($inventory_id, function ($query, $inventory_id) {
                	return $query->whereHas('order', function ($query) use ($inventory_id) {
                		$query->where('inventory_id', $inventory_id);
            });})->count();
			$old_sold_items = OrderItem::whereBetween('created_at', [Carbon::now()->subYears(2)->startOfDay(), Carbon::now()->subYear()->startOfDay()])/*->where('status',null)*/
				->when($inventory_id, function ($query, $inventory_id) {
                	return $query->whereHas('order', function ($query) use ($inventory_id) {
                		$query->where('inventory_id', $inventory_id);
            });})->count();
		}
		if ($old_sold_items == 0) {
			$percentageDifferenceOrderItems = 0;
		} else {
			$percentageDifferenceOrderItems = (($new_sold_items - $old_sold_items) / $old_sold_items) * 100;
		}

        $inventory = Inventory::count();  


        $all = [
            'marketCounts' => $inventory,
            'today_sales' => $new_sales_counts,
            'Item_count' => $new_sold_items,
            'open_orders' => $new_order_counts,
            'percentageDifferenceOrders' => $percentageDifferenceOrders,
            'percentageDifferenceOrderItems' => $percentageDifferenceOrderItems,
            'percentageDifferenceOpenOrders' => $percentageDifferenceOpenOrders,

        ];

        return response()->json($all, 200);
    }

    public function getBestSeller()
    {
        $products = ProductVariation::withCount('order_items')->with('product.photos')
            ->orderBy('order_items_count', 'desc')->get();

        if (!$products) {
            throw new InvalidArgumentException('There Is No Feedbacks Available');
        }

        return $products;
    }

    // public function getOrderStatusChart()
    // {
    //     $ordersByStatus = Order::select('status', DB::raw('COUNT(*) as order_count'))
    //         ->groupBy('status')
    //         ->get();

    //     $totalOrders = $ordersByStatus->sum('order_count');

    //     $orderStatusData = $ordersByStatus->map(function ($order) use ($totalOrders) {
    //         $percentage = ($order->order_count / $totalOrders) * 100;
    //         $order->percentage = intval(round($percentage, 2));
    //         return $order;
    //     });

    //     if (!$orderStatusData) {
    //         throw new InvalidArgumentException('There Is No Revenues Available');
    //     }

    //     return $orderStatusData;
    // }

    public function getOrderStatusChart()
    {
        $ordersByStatus = Order::select('status', DB::raw('COUNT(*) as order_count'))
            ->groupBy('status')
            ->get();
    
        $totalOrders = $ordersByStatus->sum('order_count');
    
        $allStatuses = ['processing', 'in_delivery', 'received', 'canceled', 'returned', 'replaced', 'fulfilled'];
    
        foreach ($allStatuses as $status) {
            if (!$ordersByStatus->contains('status', $status)) {
                $ordersByStatus->push((object)[
                    'status' => $status,
                    'order_count' => 0,
                    'percentage' => 0
                ]);
            }
        }
    
        $orderStatusData = $ordersByStatus->map(function ($order) use ($totalOrders) {
			if($totalOrders == 0){
				$percentage = 0;	
			}else{
				$percentage = ($order->order_count / $totalOrders) * 100;
			}
            $order->percentage = intval(round($percentage, 2));
            return $order;
        });
    
        if (!$orderStatusData) {
            throw new InvalidArgumentException('There Is No Revenues Available');
        }
    
        return $orderStatusData;
    }

    public function getSalesChart($date, $inventory_ids)
    {
        if ($inventory_ids == null) {
            $inventories = Inventory::select('id', 'name')->get();
        } else {
            $inventories = Inventory::whereIn('id', $inventory_ids)->select('id', 'name')->get();
        }

        $parts = explode('-', $date);

        $charts = [];
        foreach ($inventories as $inventory) {
            if (count($parts) === 1) {
				    
                // group by month
                $date = Carbon::create($date, null, null, null, null, null, null);
				   
                $orders = collect(Order::where('inventory_id', $inventory->id)
                    ->select(
                        'inventory_id',
                        DB::raw('MONTH(created_at) as month'),
                        DB::raw('SUM(total_price) as total')
                    )->whereYear('created_at', $date->year)
                    ->groupBy('inventory_id', 'month')
                    ->get())
                    ->keyBy('month');
				   
                $monthsRange = collect(range(1, 12));
				  
                $sales_chart = $monthsRange->map(function ($month) use ($orders, $inventory) {
                    return $orders->has($month) ? [
                        'inventory_name' => $inventory->name,
                        'label' => $month,
                        'total' => $orders->get($month)->total
                    ] : [
                        'inventory_name' => $inventory->name,
                        'label' => $month,
                        'total' => 0
                    ];
                })->keyBy('label')->values()->toArray();
				

                // $charts[$inventory->name] = $sales_chart;
                array_push($charts, [$inventory->name => $sales_chart]);
				
                // $charts[$inventory->id] = $sales_chart;
                // return $orders;
            } elseif (count($parts) === 2) {
				
                $date = Carbon::create($date, null, null, null, null, null, null);

                $orders = collect(Order::where('inventory_id', $inventory->id)
                    ->select(
                        'inventory_id',
                        DB::raw('YEAR(created_at) as year'),
                        DB::raw('MONTH(created_at) as month'),
                        DB::raw('DAY(created_at) as day'),
                        DB::raw('SUM(total_price) as total')
                    )->groupBy('inventory_id')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->groupBy('inventory_id', 'year', 'month', 'day')
                    ->get())
                    ->keyBy('day');
					
                // dd(Carbon::now()->month($month)->daysInMonth);

                $monthsRange = collect(range(1, Carbon::now()->month($date->month)->daysInMonth));

                // $monthsRange = collect(range(1, 31));

                // Fill in missing months with null values
                $sales_chart = $monthsRange->map(function ($day) use ($orders, $inventory) {
                    return $orders->has($day) ? [
                        'inventory_name' => $inventory->name,
                        'label' => $day,
                        'total' => $orders->get($day)->total
                    ] : [
                        'inventory_name' => $inventory->name,
                        'label' => $day,
                        'total' => 0
                    ];
                })->keyBy('label')->values()->toArray();


                array_push($charts, [$inventory->name => $sales_chart]);
					
                //   $charts[$inventory->name] = $sales_chart;
                // return $orders;
            } else {
                return throw new Exception('out of context');
            }
        }

        return $charts;
    }

    protected function groupByMonth($query, $year)
    {
        // group by month
        return $query->with(['orders' => function ($query) use ($year) {
            $query->whereYear('created_at', '=', $year)
                ->with('order_items')
                ->withSum('order_items', 'quantity');
        }])->get();
    }

    protected function groupByDay($query, $year, $month)
    {
        // group by day
        return $query->with(['orders' => function ($query) use ($year, $month) {
            $query->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', $month)
                ->with('order_items')
                ->withSum('order_items', 'quantity');
        }])->get();
    }

    public function getUserSalesChart($year)
    {

        //   $year = 2022; // سنة 2023

        $results = [];

        for ($month = 1; $month <= 12; $month++) {
            // عدد الطلبات في الشهر المحدد
            $orderCount = Order::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->sum('total_price');

            // إجمالي الكمية لكل طلب في الشهر المحدد
            $totalQuantity = Order::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->sum('total_quantity');

            // عدد المستخدمين الذين طلبوا في الشهر المحدد
            $userCount = User::whereHas('orders', function ($query) use ($month, $year) {
                $query->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);
            })
                ->count();

            // تشكيل النتائج
            $results[$month] = [
                'orderCount' => $orderCount,
                'totalQuantity' => $totalQuantity,
                'userCount' => $userCount
            ];
        }

        // إضافة القيم الصفرية للشهور التي لا تحتوي على بيانات
        for ($month = 1; $month <= 12; $month++) {
            if (!isset($results[$month])) {
                $results[$month] = [
                    'orderCount' => 0,
                    'totalQuantity' => 0,
                    'userCount' => 0

                ];
            }
        }

        // إرجاع النتائج
        return $results;
    }







    public function getRevenueChart($year, $month)
    {
        // if(!isset($year) && $year == null){
        //     $year = Carbon::now()->format('Y');
        // }

        if ((!isset($month) && $month == null)) {
            $revenues = collect(
                Order::query()
                    ->whereYear('created_at', $year)
                    ->select(
                        DB::raw('
                        Month(created_at) AS month,
                        SUM(total_price) AS revenues,
                        SUM(total_quantity) AS total_quantity
                    ')
                    )
                    ->groupBy('month')
                    ->get()
                    ->keyBy('month')
            )->union(
                collect(array_fill_keys(range(1, 12), [
                    'month' => 0,
                    'revenues' => 0,
                    'total_quantity' => 0
                ]))
            )->sortKeys()->all();
            // return $orders->get();
        } else if (
            (isset($month) && $month != null)
        ) {
            // return "day";
            $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;
            $allDays = range(1, $daysInMonth);
            $revenues = collect(
                Order::query()
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->select(
                        DB::raw('
                        Day(created_at) AS day,
                        SUM(total_price) AS revenues,
                        SUM(total_quantity) AS total_quantity
                    ')
                    )
                    ->groupBy('day')
                    ->get()
                    ->keyBy('day')
            )->union(
                collect(array_fill_keys(range(1, 31), [
                    'day' => 0,
                    'revenues' => 0,
                    'total_quantity' => 0
                ]))
            )->sortKeys()->all();
        }

        if (!$revenues) {
            throw new InvalidArgumentException('There Is No Revenues Available');
        }

        return $revenues;
    }




    public function orderCounts()
    {
        $year = request('year');
        $month = request('month');
        $status1 = request('status1');
        $status2 = request('status2');

        if ($year != null && $month == null) {

            $allOrders = Order::count();
			$ordersPreviousMonth = Order::whereBetween('created_at', [
																		Carbon::now()->subMonth()->startOfMonth(),
																		Carbon::now()->subMonth()->endOfMonth()
																	])->count();
			$ordersExceptThisMonth = Order::where('created_at', '<', Carbon::now()->startOfMonth())->count();
			//$ordersExceptThisMonth = Order::where('created_at', '<', Carbon::now()->startOfDay())->count();
            /*if ($ordersExceptThisMonth == 0) {
                $percentageDifference = 0;
            } else {
                $percentageDifference = (($allOrders - $ordersExceptThisMonth) / $ordersExceptThisMonth) * 100;
            }*/
			$percentageDifference = $ordersPreviousMonth == 0 ? 'N/A' : (($ordersExceptThisMonth - $ordersPreviousMonth) / $ordersPreviousMonth) * 100;
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
            return ['allOrders' => $allOrders, 'percentageDifference' => $percentageDifference, 'data1' => $data1, 'data2' => $data2];
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
    }





    public function categoryOrders()
    {
        $section_id = request('section_id');
        // $filters = request(['date_from', 'date_to']);
        // return $filters = request(['date_from', 'date_to'])->toArray();
        // check if the user has provided the dates
        if (request()->has('date_from') && request()->has('date_to')){
            $date_from = Carbon::parse(request('date_from'));
            $date_to = Carbon::parse(request('date_to'));
            // filter the categories by the orders between the dates
            $counts = Category::where('section_id', $section_id)->withCount('orders')->whereHas('orders', function ($query) use ($date_from, $date_to) {
                $query->whereBetween('order_items.created_at',  [$date_from, $date_to]);
            })->get();
        } else {
            // get all the categories with their orders count
            $counts = Category::where('section_id', $section_id)->withCount('orders')->get();
        }

        $totalOrders = $counts->sum('orders_count');

        $percentageCounts = $counts->map(function ($category) use ($totalOrders) {
			if($totalOrders == 0){
				$percentage	= 0;
			}else{
				$percentage = ($category->orders_count / $totalOrders) * 100;	
			}
            $category->percentage = $percentage;
            return $category;
        });

        $sortedCounts = $percentageCounts->sortByDesc('percentage');

        return $sortedCounts;
    }



    //     return $counts;
    // }

    public function sectionOrders()
    {

        $counts = Section::withCount('orders')->get();

        $totalOrders = $counts->sum('orders_count');
        $totalOrders = $totalOrders == 0 ? 1 : $totalOrders;
        $percentageCounts = $counts->map(function ($section) use ($totalOrders) {
            $percentage = ($section->orders_count / $totalOrders) * 100;
            $section->percentage = $percentage;
            return $section;
        });
        return $percentageCounts;
    }

    public function banHistory($user_id)
    {
        // return $user_id ;
        $ban_history = User::where('id', $user_id)->with('banHistory')->get();

        return $ban_history;
    }

    public function percentageDifference()
    {
        $dateScope = request('dateScope');
        $allUsers = User::count();
        if ($dateScope == 'Day') {
            $usersOnlyFilter = User::where('created_at', '>', Carbon::now()->startOfDay())->count();
        } elseif ($dateScope == 'Week') {
            $usersOnlyFilter = User::where('created_at', '>', Carbon::now()->startOfWeek())->count();
        } elseif ($dateScope == 'Month') {
            $usersOnlyFilter = User::where('created_at', '>', Carbon::now()->startOfMonth())->count();
        } elseif ($dateScope == null) {
            $usersOnlyFilter = User::where('created_at', '>', Carbon::now()->startOfWeek())->count();
        }
		if ($allUsers == 0){
			$percentageUsersOnlyFilter = 0;
		}else{
			$percentageUsersOnlyFilter = ($usersOnlyFilter / $allUsers) * 100;	
		}
        return ['current_users' => $allUsers, 'usersOnlyFilter' => $usersOnlyFilter, 'percentageUsersOnlyFilter' => $percentageUsersOnlyFilter];
    }
}
