<?php

namespace App\Services;

use App\Models\BanHistory;
use App\Models\FcmToken;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Review;
use App\Models\User;
use App\Models\Report;
use App\Models\Setting;
use App\Models\UserVerification;
use InvalidArgumentException;
use Illuminate\Support\Str;
use Intervention\Image\Exception\NotFoundException;
use App\Utils\PaginateCollection;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use App\Traits\FirebaseNotificationTrait;

class UserService
{
	use FirebaseNotificationTrait;

	public function __construct(protected PaginateCollection $paginatecollection) {}

	public function getAllUsers($filter_data = [], $sort_data = [], $type, $date) //si
	{
		try {
			$users = User::query()
				->withSum('orders as total_buy', 'total_price')
				->withCount('orders');

			if ($type != null && Str::lower($type) == "blocked") {
				$users = $users->where('banned', 1);
			}

			if ($type != null && $type == "top") {
				$now_date = Carbon::now();
				$users = $users->orderByDesc('total_buy')
					->whereHas('orders', function ($query) use ($now_date) {
						if (Str::lower($now_date) == 'week') {
							$query->whereWeek('created_at', $now_date->week);
						} elseif (Str::lower($now_date) == 'month') {
							$query->whereMonth('created_at', $now_date->month);
						}
					})->take(10)->get();

				$users = new LengthAwarePaginator($users, 10, 10);
				return $users;
			}

			if (!empty($filter_data)) {
				$users = $this->applyFilters($users, $filter_data);
			}

			if ((isset($sort_data['sort_key']) && isset($sort_data['sort_value'])) && ($sort_data['sort_key'] != null && $sort_data['sort_value'] != null)) {
				$users = $this->applySort($users, $sort_data);
			}

			if (!$users) {
				throw new NotFoundException('There Is No Users Available');
			}

			return $users->paginate(8);
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}

	public function getOrder($order_id, $user_id) //si
	{
		$delivery_type = Order::where('id', $order_id)->firstOrFail()->delivery_type;
		if ($delivery_type !== null) {
			$order = Order::with([
				'shipment',
				'order_items' => function ($query) {
					// Only load order_items where the status is not null
					$query->whereNotNull('status');
				},
				'order_items:id,order_id,price,quantity,group_id,status,product_variation_id',
				'order_items.product_variation.color',
				'order_items.product_variation.size',
				'order_items.product_variation.product.main_photos:id,product_id,thumbnail,main_photo',
				'order_items.product_variation.product:id,name',
				'branch',
				'employee',
				'address',
			])->where('user_id', $user_id)->findOrFail($order_id);
		} else {
			$order = Order::with([
				'shipment',
				'order_items',
				'order_items:id,order_id,price,quantity,group_id,status,product_variation_id',
				'order_items.product_variation.color',
				'order_items.product_variation.size',
				'order_items.product_variation.product.main_photos:id,product_id,thumbnail,main_photo',
				'order_items.product_variation.product:id,name',
				'branch',
				'employee',
				'address',
			])->where('user_id', $user_id)->findOrFail($order_id);
		}

		foreach ($order->order_items as $item) {
			$item->append('itemNo');
			$item->unit_price = $item->product_variation->product->pricing->value;
			if($order->coupon_id != null){
				$cpn = Coupon::find($order->coupon_id );
				if($cpn){
					$item->price = $item->price - $item->price * $cpn->percentage;
				}
			}
		}
		
		/*if($order->coupon_id != null){
				$cpn = Coupon::find($order->coupon_id );
				if($cpn){
					$order->total_price = $order->total_price - $order->total_price * $cpn->percentage;
				}
			}*/

		if ($order->type == 'kadmous') {
			$order->isKadmous = true;
		} else {
			$order->isKadmous = false;
		}


		if (!$order) {
			throw new InvalidArgumentException('User not found');
		}

		$response = [
			'order' => $order,
		];
		return $response;
	}

	public function getUserOrders(int $user_id, $filter_data) //si
	{
		$user = User::findOrFail($user_id);

		$orders = $user->orders()->with([
			'shipment:id,order_id,city,neighborhood,street',
		])->select('id', 'user_id', 'address_id', 'invoice_number', 'total_price', 'total_quantity', 'status', 'payment_method', 'created_at');

		if (!empty($filter_data)) {
			$orders = $this->applyFilters($orders, $filter_data);
		}

		if (!empty($sort_data)) {
			$orders = $this->applySort($orders, $sort_data);
		}

		$orders = $orders->paginate(6);

		return $orders;
	}

	public function getUsersReviews(int $user_id, $filter_data) //si
	{
		try {
			$user = User::findOrFail($user_id)->load('reviews');
			$reviews = $user->reviews()->with([
				'product:id,name',
				'product.main_photos',
				'user'
			]);

			if (!empty($filter_data)) {
				$reviews = $this->applyFilters($reviews, $filter_data);
			}

			if (!$reviews) {
				throw new InvalidArgumentException('Reviews not found');
			}

			return $reviews->paginate(8);
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}

	public function getUserComplaints($user_id, $filter_data) //si
	{
		try {
			$complaints = Report::where('user_id', $user_id);
			
			if (!$complaints) {
				throw new InvalidArgumentException('Complaints not found');
			}

			if (!empty($filter_data)) {
				$complaints = $this->applyFilters($complaints, $filter_data);
			}

			$complaints = $complaints->paginate(8);
			$key = Setting::where('key', 'type_of_problems')->firstOrFail();
			$locale = app()->getLocale();
			$keys = json_decode($key->value, true)[$locale];
			$type_of_problems = [];

			foreach ($keys as $key) {
				$type_of_problems[] = $key['name'];
			}

			return [
				'current_page' => $complaints->currentPage(),
				'data' => $complaints->items(),
				'first_page_url' => $complaints->url(1),
				'from' => $complaints->firstItem(),
				'last_page' => $complaints->lastPage(),
				'last_page_url' => $complaints->url($complaints->lastPage()),
				'links' => $complaints->links(),
				'next_page_url' => $complaints->nextPageUrl(),
				'path' => $complaints->path(),
				'per_page' => $complaints->perPage(),
				'prev_page_url' => $complaints->previousPageUrl(),
				'to' => $complaints->lastItem(),
				'total' => $complaints->total(),
				'type_of_problems' => $type_of_problems,
			];
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}

	public function getUserCards(int $user_id, $filter_data) //si
	{
		try {
			$user = User::where('id', $user_id)
				->with('coupons', function ($query) {

					$query->where('type', 'gift');
				})->firstOrFail();

			if (!$user) {
				throw new InvalidArgumentException('User not found');
			}

			$coupons = $user->coupons()->getQuery();

			if (!empty($filter_data)) {
				$coupons = $this->applyFilters($coupons, $filter_data);
			}

			if (!$coupons) {
				throw new Exception('coupons not found');
			}

			return $coupons->paginate(8);
		} catch (Exception $th) {
			throw new Exception($th->getMessage());
		}
	}

	public function forceDelete($user_id) //si
	{
		$user = User::findOrFail($user_id);
		$order = Order::where('user_id', $user->id)->whereIn('status', ['received', 'replaced', 'returned'])->first();

		if ($order) {
			return response()->error(['message' => 'You have some unfinshed orders, you can not delete your account if you did not complete your orders or cancel them'], 400);
		}

		$user->update([
			'is_deleted' => 1,
			'phone' => 'deleted account -' . $user->phone . '-'
		]);
		return response()->success(
			[
				'message' => 'User deleted successfully'
			],
			200
		);
	}

	public function updateemail($data, $user_id) //si
	{
		$user = User::find($user_id);
		if (!$user) {
			throw new InvalidArgumentException('There Is No Users Available');
		}

		if ($user != null) {
			$user->update(['email' => $data]);
		} else {
			return ' this id does not exit to modify ';
		}
	}

	public function updatename(array $data, $user_id): User //si
	{
		$user = User::find($user_id);
		
		if (!$user) {
			throw new InvalidArgumentException('There Is No Users Available');
		}

		$user->update([
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
		]);

		return $user;
	}

	public function updatephone($data, int $user_id): User //si
	{
		$user = User::findOrFail($user_id);
		$verify_code = (string) random_int(1000, 9999);
		UserVerification::create([
			'user_id' => $user->id,
			'verify_code' => $verify_code,
			'expired_at' => now()->addMinutes(15)
		]);
		ob_start();
		$ch = curl_init();
		curl_setopt(
			$ch,
			CURLOPT_URL,
			"https://bms.syriatel.sy/API/SendTemplateSMS.aspx?user_name=XO1&password=P@1234567&template_code=XO1_T1&param_list=" . $verify_code . "&sender=XO&to=$data"
		);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		if (curl_exec($ch) === false) {
			echo 'Curl error: ' . curl_error($ch);
		}

		curl_close($ch);
		ob_end_clean();

		return $user;
	}

	public function updatepassword(array $data, int $user_id) //si
	{
		$user = User::findOrFail($user_id);
		if ($user != null) {
			$user_password = $user->password;
			$password = $data['old_password'];

			if (Hash::check($password, $user_password)) {

				$user->password = Hash::make($data['new_password']);
				$user->save();
			} else {
				throw new Exception('Wrong Password');
			}
		} else {
			return response()->json('Unauthorized', 403);
		}
	}

	protected function applyFilters($query, array $filters) //si
	{
		$appliedFilters = [];
		foreach ($filters as $attribute => $value) {
			$column_name = Str::before($attribute, '_');
			$method = 'filterBy' . Str::studly($column_name);
			if (method_exists($this, $method) && isset($value) && !in_array($column_name, $appliedFilters)) {
				$query = $this->{$method}($query, $filters);
				$appliedFilters[] = $column_name;
			}
		}

		return $query;
	}

	protected function filterByDetails($query, $filter_data)
	{
		return $query->where('details', $filter_data['details']);
	}

	protected function filterByTitle($query, $filter_data)
	{
		return $query->where('type', $filter_data['title']);
	}

	protected function filterByType($query, $filter_data)
	{
		return $query->where('type', $filter_data['type']);
	}

	protected function filterByValue($query, $filter_data)
	{
		return $query->where('amount_off', $filter_data['value']);
	}

	protected function filterByLast($query, $filter_data)
	{
		return $query->whereDate('last_recharge', $filter_data['last_recharge']);
	}

	protected function filterByCreated($query, $filter_data)
	{
		return $query->whereDate('created_at', $filter_data['created']);
	}
	protected function filterBySearch($query, $filter_data)
	{
		$search = $filter_data['search'];
		return $query->where('email', 'like', '%' . $search . '%')
			->orWhere('phone', 'like', '%' . $search . '%');
	}

	protected function filterByOrders($query, $filter_data)
	{
		$orders_count = $filter_data['orders'] ?? 0;
		return $query->havingRaw('orders_count >= ?', [$orders_count])
			->orderBy('orders_count', 'desc');
	}

	protected function filterByStatus($query, $filter_data)
	{
		return $query->where('status', $filter_data['status']);
	}

	protected function filterByPricing($query, $filter_data)
	{
		$pricing = $filter_data['pricing'] ?? 0;

		return $query->whereHas('orders', function ($query2) use ($pricing) {
			$query2->whereNull('deleted_at')
				->havingRaw('SUM(orders.total_price) > ?', [$pricing]);
		});
	}

	protected function filterByRating($query, $filter_data)
	{
		return $query->where('rating', '=', $filter_data['rating']);
	}

	protected function filterByContent($query, $filter_data)
	{
		return $query->where('comment', 'LIKE', '%' . $filter_data['content'] . '%');
	}

	protected function filterByDate($query, $filter_data)
	{
		$date_min = $filter_data['date_min'] ?? 0;
		$date_max = $filter_data['date_max'] ?? date('Y-m-d');

		return $query->whereBetween('created_at', [$date_min, $date_max]);
	}

	protected function applySort($query, array $sort_data)
	{
		return $query->orderBy($sort_data['sort_key'], $sort_data['sort_value']);
	}

	public function Ban_user($user, $start_date,  $end_date, $reason) //si
	{
		if (!$user) {
			throw new InvalidArgumentException('There Is No Users Available');
		}

		$fcm_tokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();
		$user->update([
			'banned' => 1
		]);
		$title = [
			"ar" => "تم إيقاف حسابك مؤقتا حتى تاريخ " . $end_date,
			"en" => "Your account is temporary banned until " . $end_date
		];
		$body = [
			"ar" => $reason,
			"en" => $reason
		];
		foreach ($fcm_tokens as $fcm) {
			$fcm_token = FcmToken::where([['fcm_token', $fcm], ['user_id', $user->id]])->first();
			if ($fcm_token->lang == 'en') {
				$this->send_notification(
					$fcm,
					'Your account is temporary banned until ' . $end_date, 
					$reason, 
					'Ban',
					'flutter_app'
				);
			} else {
				$this->send_notification(
					$fcm,
					'تم إيقاف حسابك مؤقتا حتى تاريخ ' . $end_date,
					$reason, 
					'Ban',
					'flutter_app'
				);
			}
		}

		$user->notifications()->create([
			'user_id' => $user->id,
			'type' => 'Ban',
			'title' => $title,
			'body' => $body
		]);

		$ban_history = BanHistory::create([
			'user_id' => $user->id,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'reason' => $reason,
		]);
		return $ban_history;
	}

	public function UnBan_user($user) //si
	{
		if (!$user) {
			throw new InvalidArgumentException('There Is No Users Available');
		}

		$user->update([
			'banned' => 0
		]);
		$fcm_tokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();
		$title = [
			"ar" => "تم إنهاء فترة إيقاف حسابك",
			"en" => "Your account is no longer banned"
		];
		$body = [
			"ar" => "تم إنهاء فترة إيقاف حسابك",
			"en" => "Your account is no longer banned"
		];
		foreach ($fcm_tokens as $fcm) {
			$fcm_token = FcmToken::where([['fcm_token', $fcm], ['user_id', $user->id]])->first();
			if ($fcm_token->lang == 'en') {
				$this->send_notification(
					$fcm,
					"Your account is no longer banned",
					"Your account is no longer banned",
					'Notifications',
					'flutter_app'
				);
			} else {
				$this->send_notification(
					$fcm,
					'تم إيقاف حسابك مؤقتا',
					"Your account is no longer banned",
					'Notifications',
					'flutter_app'
				);
			}
		}

		$user->notifications()->create([
			'user_id' => $user->id,
			'type' => 'Notifications',
			'title' => $title,
			'body' => $body
		]);
		return $user;
	}

	public function createToken($id)
	{
		$user = User::find($id);
		$token = $user->createToken('authToken', ['*'])->plainTextToken;
		// return response()->json($token, 200);

		if (!$user) {
			throw new InvalidArgumentException('There Is No Users Available');
		}

		return $token;
	}

	public function ban_histroy($user) //si
	{
		$user = BanHistory::where('user_id', $user->id)->with('user')->latest()->paginate(6);
		
		if (!$user) {
			throw new InvalidArgumentException('There Is No Users Available');
		}

		return $user;
	}

	public function UserCounts() //si
	{
		$allUsers = User::count();
		$allReviews = Review::count();
		$usersOnlyFilter = User::where('created_at', '>', Carbon::now()->startOfWeek())->count();

		if ($allUsers != 0) {
			$percentageUsers = ($usersOnlyFilter / $allUsers) * 100;
		} else {
			$percentageUsers = 0;
		}

		return ['allUsers' => $allUsers, 'allReviews' => $allReviews, 'percentageUsers' => $percentageUsers];
	}
}
