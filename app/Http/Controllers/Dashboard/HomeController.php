<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Group;
use App\Models\Version;
use App\Services\FeedbackService;
use App\Services\HomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomNotification;
use Carbon\Carbon;
use Kutia\Larafirebase\Messages\FirebaseMessage;
use App\Traits\FirebaseNotificationTrait;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendFirebaseNotificationJob;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
	
	use FirebaseNotificationTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct(
        protected FeedbackService $feedbackService,
        protected HomeService $homeService
    ) {
    
    }

	public function home(Request $request){
		
		if( 'a' == 'a'){

		/*$ip_address = request()->ip();

		$full_url = 'http://ip-api.com/json/'.$ip_address;
		//$full_url = 'http://ip-api.com/json/'.$request->user_ip;
		$response = (Http::get($full_url))->json();
		if($response['country'] == 'Syria' || $response['country'] == 'syria' ){*/
			
			
		/*$validate = Validator::make(
			$request->only('version_number','op_sys'),
			[
			  'version_number' => 'required|string|max:10',
			  'op_sys' => 'required|string|max:25',
			],
		);

		if ($validate->fails()) {
			return response()->json($validate->errors(), 422);
		}
		
		$version = Version::where([['version_number',$validate->validated()['version_number']],
								   ['op_sys',$validate->validated()['op_sys']]])
			->firstOrFail();
		
		if($version->is_deployed == 1){*/
			return response()->json([
				'map_init' => [33.5085, 36.2931],
				'country' => 'home',
				'currency' => 'SYP',
				'country_code' => '+964',
				'payment_methods'=> ['cod'=>['url1'=> null , 
											 'url2' => null,
											 'name' => 'Cash on delivery',
											 'value' => 'cod',
											 'number' => 0,
											 'photo_url' => 'https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/cod?_a=E' ],
									 'payment_method_1'=>['url1' => 'v1/syriatel-cash/payment-request', 
														  'url2' => 'v1/syriatel-cash/payment-confirmation',
									 					  'name' => 'Syriatel cash',
														  'value' => 'syriatel-cash',
														  'number' => 1,
														  'photo_url' => 'https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/s-cash?_a=E'],
									 'payment_method_2'=>['url1' => 'v1/mtn-cash/payment-initiate', 
														  'url2' => 'v1/mtn-cash/payment-confirmation',
														  'name' => 'Cash Mobile',
														  'value' => 'mtn-cash',
														  'number' => 2,
														  'photo_url' => 'https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/m-cash?_a=E' ],
									 'payment_method_3'=>['url1' => null, 
														  'url2' => null,
														  'name' => 'ECash',
														  'value' => 'ecash',
														  'number' =>3,
														  'photo_url' => 'https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/e-cash?_a=E']], // payment_method_1 is syriatel, payment_method_2 is mtn , payment_method_3 is ecash
				'delivery_types' => ['other'=> ['name'=>'kadmous'], 'xo_dedlivery' =>['name' => 'xo_dedlivery' ]],
				'xo_contact_number' => '+964987654321' ,
				'base_url' => 'https://api.xo-textile.sy/public/api/'
			]);
		}else{
			return response()->json([
				'country' => 'other',
				'currency' => 'AED',
				'country_code' => '+60',
				'payment_methods'=> ['cod'],
				'delivery_methods' => ['xo_dedlivery'],
				'xo_contact_number' => '+964987654321' ,
				'base_url' => 'https://api.xo-textile.com'
			]);	
		}

		return response()->json($response['country']);
	}
    // public function __construct()
    // {
    // $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
	
	
	public function newMobileVersion(Request $request){
	
		$validate = Validator::make(
			$request->only('version_number','op_sys'),
			[
			  'version_number' => 'required|string|max:10',
			  'op_sys' => 'required|string|max:25',
			],
		);

		if ($validate->fails()) {
			return response()->json($validate->errors(), 422);
		}
		
		$version = Version::create([
			'version_number' => $validate->validated()['version_number'],
			'op_sys' => $validate->validated()['op_sys'],
			'is_deployed' => false,
		]);
		
		return response()->json('New mobile version created successfully', 201);
		
	}
	
	public function updateDeploymentStatus(Request $request){
		$validate = Validator::make(
			$request->only('version_number','op_sys','status'),
			[
			  'version_number' => 'required|string|max:10',
			  'op_sys' => 'required|string|max:25',
			  'status' => 'required|in:0,1'
			],
		);

		if ($validate->fails()) {
			return response()->json($validate->errors(), 422);
		}
		
		$version = Version::where([['version_number',$validate->validated()['version_number']],
								   ['op_sys',$validate->validated()['op_sys']]])
			->firstOrFail();
		$version->update(['is_deployed'=>$validate->validated()['status']]);
				
		return response()->json('mobile version was updated successfully', 200);

	}
	
    public function index()
    {
        return view('home');
    }

    public function allFeedback()
    {
        $feedback = $this->feedbackService->getAllFeedbacks();

        return response()->success(
            $feedback,
            Response::HTTP_OK
        );
    }

    public function bestSeller()
    {
        $products = $this->homeService->getBestSeller();

        return response()->success(
            $products,
            Response::HTTP_OK
        );
    }

    public function revenuesChart(Request $request)
    {
        $year = request('year') ?? Carbon::now()->format('Y');
        $month = request('month');
        $revenues = $this->homeService->getRevenueChart($year, $month);

        return response()->success(
            $revenues,
            Response::HTTP_OK
        );
    }

    public function copmareUserSales(Request $request)
    {
        $year = request('year') ?? Carbon::now()->format('Y');
        // $month = $request->input('month');

        $revenues = $this->homeService->getUserSalesChart($year);

        return response()->success(
            $revenues,
            Response::HTTP_OK
    );
    }

    public function orderStatusChart()
    {
        $revenues = $this->homeService->getOrderStatusChart();

        return response()->success(
            $revenues,
            Response::HTTP_OK
        );
    }

    public function copmareSales(Request $request)
    {
        try {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $inventory_ids = request('inventory_ids');
            // $to_date = Carbon::createFromFormat('Y-m',$to_date);
            // dd($date->year);

            $from_chart = $this->homeService->getSalesChart($from_date, $inventory_ids);
            // $from_chart = array();
            // foreach ($from as $key => $value) {
            //     $item = array($key => $value);
            //     $from_chart = array_merge($from_chart, $item);
            //     // array_push($from_chart, array($key => $value));
            //     // $from_chart[$key] = $value;
            // }

            $to_chart = $this->homeService->getSalesChart($to_date, $inventory_ids);

            return response()->success([
                'from_chart' => $from_chart,
                'to_chart' =>  $to_chart
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,

            );
        }
    }


    public function gethomeCount()
    {
        $counts = $this->homeService->getHomeCounts();

        return response()->success(
            $counts,
            Response::HTTP_OK
        );
    }


    public function orderCounts()
    {
        $from_date = request()->input('from_date');
        $to_date = request()->input('to_date');
        $order_status = request('status');

        $counts = $this->homeService->orderCounts($order_status);

        return response()->success(
            $counts
        , Response::HTTP_OK);
    }


    public function categoryOrders()
    {
        $counts = $this->homeService->categoryOrders();

        return response()->success(
            $counts
        , Response::HTTP_OK);
    }

    public function sectionOrders()
    {
        $counts = $this->homeService->sectionOrders();

        return response()->success(
            $counts
        , Response::HTTP_OK);
    }


    public function sendCustomNotification(Request $request)
    {
        
        // $type= $request->type;
         $title=$request->title ;
         $body= $request->body;
         $type= $request->type;
         $userIds= $request->userIds;

         if($type =='all_customers'){
            $users=User::with('fcm_tokens')
            ->get();

            // return $users;

        }
        elseif($type =='top_customers'){
            $users=User::with('fcm_tokens')->withCount('orders')
            ->orderBy('orders_count','desc')
            ->limit(10)->get();
        }
        elseif($type =='new_customers'){
            $users=User::where('created_at', '>', Carbon::now()->startOfMonth())
            ->get();
            
        }
        elseif($type =='custom'){
            $users = User::with('fcm_tokens')->whereIn('id', $userIds)
            ->get(); 
            
        }
		
		//////////test for optimized way to send notifications/////////
		
		$notificationData = [];
		foreach ($users as $user) {
			$fcmTokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();

			foreach ($fcmTokens as $fcmToken) {
				// Dispatch the job instead of calling the method directly
				SendFirebaseNotificationJob::dispatch(
					$fcmToken,
					$title,
					$title,
					$body,
					$body,
					'notification_page',
					'flutter_app',
					null // Priority is optional; pass null if not needed
				);
			}

			// Prepare data for batch insertion
			$notificationData[] = [
				'user_id' => $user->id,
				'type' => "order_page",
				'title' => $title,
				'body' => $body
			];
		}

		// Batch insert notifications
		DB::table('notifications')->insert($notificationData);
		
		
		/////////End of test for optimized way to send notifications////////
		/*
		foreach ($users as $user){
			$fcm_tokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();
			foreach($fcm_tokens as $fcm){
				$this->send_notification($fcm, 
										 'تم إعادة المبلغ إلى حسابك بنجاح',
										 'Your money refunded to you successfully',
										 'تم إعادة المبلغ إلى حسابك بنجاح',
										 'Your money refunded to you successfully', 
										 'user_page', 
										 'flutter_app');
			}

			$user->notifications()->create([
				'user_id'=>$user->id,
				'type'=> "order_page", // 1 is to redirect to the orders page
				'title'=>$title,
				'body'=>$body
			]);
			
		}
		*/
        //return $users;

        //Notification::send($users, new CustomNotification(
        //$title,
        //$body
    //));
}
// sendGroupNotification
public function sendGroupNotification(Request $request)
{
    
    //  $title=$request->title ;
    $group_slug= $request->group_slug;
    $body= $request->body;
    $type= $request->type;
    $userIds= $request->userIds;
    $group= Group::where('slug',$group_slug)->first();
    $title = $group->name;
     

    if($type =='all_customers'){        
		$users=User::with('fcm_tokens')->whereHas('fcm_tokens')
            ->get();

            // return $users;
        }
         elseif($type =='top_customers'){
            $users=User::with('fcm_tokens')
				->whereHas('fcm_tokens')
				->withCount('orders')
            	->orderBy('orders_count','desc')
            	->limit(10)->get();
        }
        elseif($type =='new_customers'){
            $users=User::where('created_at', '>', Carbon::now()->startOfMonth())
            ->whereHas('fcm_tokens')
            ->get();
            
        }
        elseif($type =='custom'){
            $users = User::with('fcm_tokens')->whereIn('id', $userIds)
            ->whereHas('fcm_tokens')
            ->get(); 
            
        }
	
	/////////////////////////////////////////////////////////////////////////////////////////////////
	
	$notificationData = [];
	
	foreach ($users as $user) {
		$fcmTokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();

		foreach ($fcmTokens as $fcmToken) {
			// Dispatch the job instead of calling the method directly
			SendFirebaseNotificationJob::dispatch(
				$fcmToken,
				$title,
				$title,
				$body,
				$body,
				'notification_page',
				'flutter_app',
				null // Priority is optional; pass null if not needed
			);
		}

		// Prepare data for batch insertion
		$notificationData[] = [
			'user_id' => $user->id,
			'type' => "order_page",
			'title' => $title,
			'body' => $body
		];
	}

	// Batch insert notifications
	DB::table('notifications')->insert($notificationData);
		
	
	
	/////////////////////////////////////////////////////////////////////////////////////////////////
    /*Notification::send($users, new CustomNotification(
    $title,
    $body
));*/
}

public function sendCouponNotification(Request $request)
{
    
    //  $title=$request->title ;
    $coupon_id= $request->coupon_id;
     $body= $request->body;
     $type= $request->type;
     $userIds= $request->userIds;
 
     $coupon = Coupon::find($coupon_id);
     $title = $coupon->code;

     if($type =='all_customers'){
        $users=User::whereHas('fcm_tokens')
        ->get();
    }
    elseif($type =='top_customers'){
        $users=User::withCount('orders')
         ->whereHas('fcm_tokens')
        ->orderBy('orders_count','desc')
        ->limit(10);
    }
    elseif($type =='new_customers'){
        $users=User::where('created_at', '>', Carbon::now()->startOfMonth())
         ->whereHas('fcm_tokens')
        ->get();
        
    }
    elseif($type =='custom'){
        $users = User::whereIn('id', $userIds)
         ->whereHas('fcm_tokens')
        ->get(); 
        
    }
	
	//////////test for optimized way to send notifications/////////
		
		$notificationData = [];
		foreach ($users as $user) {
			$fcmTokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();

			foreach ($fcmTokens as $fcmToken) {
				// Dispatch the job instead of calling the method directly
				SendFirebaseNotificationJob::dispatch(
					$fcmToken,
					$title,
					$title,
					$body,
					$body,
					'notification_page',
					'flutter_app',
					null // Priority is optional; pass null if not needed
				);
			}

			// Prepare data for batch insertion
			$notificationData[] = [
				'user_id' => $user->id,
				'type' => "order_page",
				'title' => $title,
				'body' => $body
			];
		}

		// Batch insert notifications
		DB::table('notifications')->insert($notificationData);
	
	
    /*Notification::send($users, new CustomNotification(
    $title,
    $body
));*/
}


public function banHistory(Request $request)
{
    $user_id = request('user_id');
    $ban_istory = $this->homeService->banHistory($user_id);
        return response()->success(
            $ban_istory
        , Response::HTTP_OK);
}


public function percentageDifference()
{

    $users = $this->homeService->percentageDifference();

    return response()->json([$users], 200);
}


    }

