<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use App\Traits\FirebaseNotificationTrait;
use App\Enums\Roles;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class UserController extends Controller
{
    use FirebaseNotificationTrait;

    public function __construct(
        protected UserService $userService
    ) {}


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)//si
    {
        try {
            $filter_data = $request->only(['orders', 'pricing', 'search', 'created']);
            $type = request('type');
            $date = request(['date']);
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

    public function showUserOrders(Request $request) //si
    {
        try {
            $employee = auth('api-employees')->user();

            if (!$employee) {
                throw new Exception('Employee does not exist');
            }

            if ($employee->hasRole(Roles::MAIN_ADMIN) || $employee->hasRole(Roles::DELIVERY_ADMIN)) {
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
            } else {
                throw new Exception('Unauthorized', 403);
            }
        } catch (Exception $e) {
            return response()->error(
                [
                    'error' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function showUserReviews(Request $request) //si
    {
        try {
            $user_id  = request('user_id');
            $filter_data = $request->only(['created', 'content', 'rating']);
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

    public function showUserComplaints(Request $request) //si
    {
        try {
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

    public function showUsersCards(Request $request) //si
    {
        try {
            $user_id  = request('user_id');
            $filter_data = $request->only(['status', 'created', 'last', 'value']);
           
            if ($filter_data['status'] == 'active') {
                $filter_data['status'] = 1;
            } elseif ($filter_data['status'] == 'inactive') {
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

    public function deleteUser() //si
    {
        try {
            $user_id  = request('user_id');
            return $this->userService->forceDelete($user_id);

        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    public function Ban_user()//si
    {

        $user = User::findOrFail(request('user_id'));
        $start_date = request('start_date');
        $end_date = request('end_date');
        $reason = request('reason');
        $type = request('type');

        $user = $this->userService->Ban_user($user, $start_date, $end_date, $reason, $type);
        return response()->success([
            'message' => "User bannded successfully",
            'user' => $user
        ], Response::HTTP_OK);
    }

    public function UnBan_user()//si
    {
        $user = User::findOrFail(request('user_id'));
        $user = $this->userService->UnBan_user($user);
        return response()->success([
            'message' => "User Unbannded successfully",
            'user' => $user
        ], Response::HTTP_OK);
    }

    public function ban_histroy() //si
    {
        $user = User::find(request('user_id'));
        $user = $this->userService->ban_histroy($user);
        return response()->success(
            $user,
            Response::HTTP_OK
        );
    }

    public function UserCounts() //si
    {
        $counts = $this->userService->UserCounts();
        return response()->success(
            $counts,
            Response::HTTP_OK
        );
    }
	
		public function export()
    {
        /*return Product::select("id", "name", "description")
        ->with(['product_variations' => function ($query) {
            $query->select('id');
        }])->get();*/
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
