<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FcmToken;
use App\Notifications\BanUserNotification;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Models\Setting;
use Exception;
use App\Traits\FirebaseNotificationTrait;
use App\Enums\Roles;

class UserController extends Controller
{
 	use FirebaseNotificationTrait;

    public function __construct(
        protected UserService $userService
    ) {
    
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $filter_data = $request->only(['orders', 'pricing','search', 'created']);
            // type : top, blocked
            $type = request('type');
            // date : week, month
            $date = request(['date']);
            // sort key : total_buy, orders_count, created_at
            $sort_data = $request->only(['sort_key', 'sort_value',]);

            $users = $this->userService->getAllUsers($filter_data, $sort_data, $type, $date);

            return response()->success(
                $users,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    public function getOnlineUsers()
    {


        $users = $this->userService->getOnlineUsers();

        return response()->success(
            $users,
            Response::HTTP_OK
        );
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function export()
    {
        // return Excel::download(new UsersExport, 'users.xlsx');
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
            $validate = Validator::make(
                $request->all(),
                [
                    'user.name' => 'required|max:255',
                    'user.email' => 'required|email|unique:users',
                    'user.password' => 'required|max:255',
                    'user.address' => 'required|max:255',
                    'user.phone' => 'required|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    [
                        'errors' => $validate->errors()
                    ],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();
            $user_data = $validated_data['user'];

            $user = $this->userService->createUser($user_data);

            return response()->success(
                [
                    'message' => 'User Is Created',
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        try {
            $user_id  = request('user_id');
            $user = $this->userService->getUser($user_id);

            return response()->success(
                    $user,
                Response::HTTP_FOUND
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function showOrder()
    {
        try {
            // $user_id = auth('sanctum')->user()->id;
            $order_id = request('order_id');

            $order = $this->userService->getOrder($order_id);

            return response()->success(
                [
                    'order' => $order
                ],
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                [
                    'error' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

	public function showUserOrders(Request $request)
    {
        try {
             $employee = auth('api-employees')->user();
		
			//$employee = Employee::find(10);
			if(!$employee){
				throw new Exception('Employee does not exist');
			}

			if (!$employee->hasRole(Roles::MAIN_ADMIN) ) {
				throw new Exception('Unauthorized',403);
			}
			
			$validate = Validator::make(
            $request->only('user_id'),
            [
                'user_id' => 'required|integer|exists:users,id',
            ]
        );

        if ($validate->fails()) {
            return response()->error($validate->errors(), 422);
        }
            
			
			$user_id  = $validate->validated()['user_id'];
            
			$filter_data = $request->only(['created', 'status']);
            $orders = $this->userService->getUserOrders($user_id, $filter_data);

            return response()->success(
                $orders,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                [
                    'error' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function showUserHistories(Request $request)
    {
        try {

            $user_id  = request('user_id');
            $filter_data = $request->only(['content', 'rating']);

            $histories = $this->userService->getUsersHistories($user_id, $filter_data);

            return response()->success(
                $histories,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                [
                    'error' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function showUserReviews(Request $request)
    {
        try {

            $user_id  = request('user_id');
            $filter_data = $request->only(['created','content', 'rating']);

            $reviews = $this->userService->getUsersReviews($user_id, $filter_data);

            return response()->success(
                $reviews,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                [
                    'error' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function showUserFeedbacks(Request $request)
    {
        try {
            // $user = auth('sanctum')->user();
            // $user_id  =$user->id;

            // type : solved, opened
            $user_id  = request('user_id');
            $filter_data = $request->only(['status', 'created', 'type']);

            $feedbacks = $this->userService->getUserFeedbacks($user_id, $filter_data);

            return response()->success(
                $feedbacks,
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                [
                    'error' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function showUserComplaints(Request $request)
    {
        try {
            // $user = auth('sanctum')->user();
            // $user_id  =$user->id;

            // type : solved, opened
            $user_id  = request('user_id');
            $filter_data = $request->only(['status', 'created', 'title']);

            $complaints = $this->userService->getUserComplaints($user_id, $filter_data);

            return response()->success(
                $complaints,
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                [
                    'error' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function showUsersCards(Request $request)
    {
        try {
            // type : solved, opened
            $user_id  = request('user_id');
            $filter_data = $request->only(['status', 'created', 'last', 'value']);
				if(	$filter_data['status'] == 'active'){
					$filter_data['status'] = 1;
				}elseif($filter_data['status'] == 'inactive'){
					$filter_data['status'] = 0;
				}
            $cards = $this->userService->getUserCards($user_id, $filter_data);

            return response()->success(
                $cards,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            $user_id  = $user->id;

            $validate = Validator::make(
                $request->all(),
                [
                    'user.name' => 'sometimes|max:255',
                    'user.email' => 'sometimes|email|unique:users',
                    'user.password' => 'sometimes|max:255',
                    'user.address' => 'sometimes|max:255',
                    'user.phone' => 'sometimes|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    [
                        'errors' => $validate->errors()
                    ],
                    Response::HTTP_OK
                );
            }

            $validated_data = $validate->validated();
            $user_data = $validated_data['user'];

            $user = $this->userService->updateUser($user_data, $user_id);

            return response()->success(
                [
                    'message' => 'User deleted successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
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
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $user = auth('sanctum')->user();
            $user_id  = $user->id;
            $userService = $this->userService->delete($user_id);

            return response()->success(
                [
                    'message' => 'User deleted successfully'
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
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $user = auth('sanctum')->user();
            $userService = $this->userService->forceDelete( $user->id);

            return response()->success(
                [
                    'message' => 'User deleted successfully'
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

    public function deleteUser()
    {
        try {
            $user_id  = request('user_id');
            $userService = $this->userService->delete($user_id);

            return response()->success(
                [
                    'message' => 'User deleted successfully'
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

    public function Ban_user()
    {

        $user = User::find(request('user_id'));
        // return $user ;

        $start_date = request('start_date');
        $end_date = request('end_date');
        $reason = request('reason');

        $user = $this->userService->Ban_user($user, $start_date,  $end_date, $reason);
        // $key = Setting::where('key', 'BanUserNotification')->first();
        // $value = json_decode($key->value);
        // $title = $value->title;
        // $body = $value->body;
        // Notification::send($user, new BanUserNotification(
        //     $title,
        //     $body
        // ));

        // return response()->json([$user, 'message' => "User bannded successfully"], 200);
        return response()->success(
            $user,
            Response::HTTP_OK
        );  
    }

    public function UnBan_user()
    {

        $user = User::find(request('user_id'));
        $user = $this->userService->UnBan_user($user);

        return response()->success(
            $user,
            Response::HTTP_OK
        );   }

    public function ban_histroy()
    {

        $user = User::find(request('user_id'));
        $user = $this->userService->ban_histroy($user);
        return response()->success(
            $user,
            Response::HTTP_OK
        );
    }
     // UserCounts
     public function UserCounts()
     {

        //  $user = User::find(request('user_id'));
         $counts = $this->userService->UserCounts();
         return response()->success(
            $counts,
            Response::HTTP_OK
        );
     }
	/*
	public function majd(){
	$this->send_notification('', 
												 'تم إنشاء طلب شراء جديد',
												 'A new order was createdy',
												 'تم إنشاء طلب شراء جديد',
												 'A new order was created', 
												 'dashboard_orders', 
												 'flutter_app');
						}	
	}*/
}
