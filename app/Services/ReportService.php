<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\FcmToken;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Inventory;
use App\Models\Role;
use App\Services\SettingService;
use App\Traits\FirebaseNotificationTrait;
use Exception;
use Illuminate\Support\Str;
use App\Enums\Roles;

class ReportService
{
    use FirebaseNotificationTrait;

    public function __construct(
        protected SettingService $settingService
    ) {}

    public function getAllReports($filter_data)//si
    {
        $employee = auth('api-employees')->user();

        if (!$employee) {
            throw new Exception('Employee does not exist');
        }

        $sender_account = $employee->account;
        
        if (!$sender_account) {
            throw new Exception('Employee does not have any account');
        }
        
        $sender_role = $sender_account->roles->first();
        
        if (!$sender_role) {
            throw new Exception('Employee does not have any role');
        }
        
        $reports = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->orderBy('created_at', 'desc');
        $reports = $this->applyFilters($reports, $filter_data)->paginate(6);
        $modifiedReports = $reports->getCollection()->map(function ($report) {
            $i = Inventory::find($report->inventory_id)->first();
            $name = $i->name ?? null;
            $report->from = $name;
            unset($report->inventory_id); // Remove the original 'inventory_id' attribute
            return $report;
        });

        $reports = new \Illuminate\Pagination\LengthAwarePaginator(
            $modifiedReports,
            $reports->total(),
            $reports->perPage(),
            $reports->currentPage(),
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
            ]
        );
        $inventories = Inventory::select('id', 'name')->get();
        return [
            'inventories' => $inventories,
            'current_page' => $reports->currentPage(),
            'data' => $reports->items(),
            'first_page_url' => $reports->url(1),
            'from' => $reports->firstItem(),
            'last_page' => $reports->lastPage(),
            'last_page_url' => $reports->url($reports->lastPage()),
            'links' => $reports->links(),
            'next_page_url' => $reports->nextPageUrl(),
            'path' => $reports->path(),
            'per_page' => $reports->perPage(),
            'prev_page_url' => $reports->previousPageUrl(),
            'to' => $reports->lastItem(),
            'total' => $reports->total(),
        ];
    }

    public function getAllUserReports($filter_data)//si
    {
        $employee = auth('api-employees')->user();

        if (!$employee) {
            throw new Exception('Employee does not exist');
        }

        if (!$employee->hasRole(Roles::MAIN_ADMIN)) {
            throw new Exception('Unauthorized', 403);
        }

        $sender_account = $employee->account;
        
        if (!$sender_account) {
            throw new Exception('Employee does not have any account');
        }

        $sender_role = $sender_account->roles->first();
        
        if (!$sender_role) {
            throw new Exception('Employee does not have any role');
        }

        $sender_role->reports()->get();
        $reports = $sender_role->reports()->with('user')->whereNotNull('user_id')->orderBy('created_at', 'desc');
        $reports = $this->applyFilters($reports, $filter_data)->paginate(6);

        $key = Setting::where('key', 'type_of_problems')->first();

        if ($key !== null) {
            $data = json_decode($key->value);

            $allOptions = [];

            foreach ($data->en as $problemType) {
                $allOptions = array_merge($allOptions, $problemType->options);
            }

            // Remove duplicates and sort the options alphabetically
            $uniqueOptions = array_unique($allOptions);
            sort($uniqueOptions);
        }
        
        return [
            'current_page' => $reports->currentPage(),
            'data' => $reports->items(),
            'first_page_url' => $reports->url(1),
            'from' => $reports->firstItem(),
            'last_page' => $reports->lastPage(),
            'last_page_url' => $reports->url($reports->lastPage()),
            'links' => $reports->links(),
            'next_page_url' => $reports->nextPageUrl(),
            'path' => $reports->path(),
            'per_page' => $reports->perPage(),
            'prev_page_url' => $reports->previousPageUrl(),
            'to' => $reports->lastItem(),
            'total' => $reports->total(),
            'type_of_problems' => $uniqueOptions,
        ];

        return $reports;
    }

    //reprort by employee to spicific roles

    public function createReport($request, $employee) //si
    {
        $sender_account = $employee->account;

        if (!$sender_account) {
            throw new Exception('Employee does not have any account');
        }

        $sender_role = $sender_account->roles->first();

        if (!$sender_role) {
            throw new Exception('Employee does not have any role');
        }

        $report = Report::create([
            'employee_id' => $employee->id,
            'user_id' => null,
            'order_id' => null,
            'reply' => null,
            'reply_by' => null,
            'type' => $request->type ?? "other",
            'inventory_id' => $employee->inventory->id ?? null,
            'content' => $request->content,
            'status' => 'open',
            'sender_role' => $sender_role->name,
        ]);

        if ($sender_role->name == 'delivery_boy') {
            $report->roles()->sync(Role::where('name', 'delivery_admin')->first()->id);
        } else {
            $report->roles()->sync(Role::where('name', 'main_admin')->first()->id);
            $roles = $request->roles;
            $report->roles()->syncWithoutDetaching($roles);
        }

        $recipients = Employee::whereHas('account', function ($query) use ($request) {
            $query->whereHas('roles', function ($query) use ($request) {
                $query->whereIn('roles.id', $request->roles);
            });
        })->get();

        foreach ($recipients as $recipient) {

            $fcm_tokens = $recipient->fcm_tokens()->pluck('fcm_token')->toArray();

            foreach ($fcm_tokens as $fcm) {
                $this->send_notification(
                    $fcm,
                    $request->type,
                    $request->content,
                    'report_page,'.$report->id,
                    'dashboard'
                );
            }

            $recipient->notifications()->create([
                'employee_id' => $recipient->id,
                'type' => "report_page,".$report->id,
                'title' => ["ar"=>$request->type,"en"=>$request->type],
                'body' => ["ar"=>$request->content,"en"=>$request->content]
            ]);
        }

        return $report;
    }

    public function createOrderReport($request, $user, $order)//si
    {
        $report = Report::create([
            'employee_id' => null,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'reply' => null,
            'reply_by' => null,
            'type' => $request->type ?? "other",
            'rate' => $request->rate,
            'from' => null,
            'content' => $request->content,
            'status' => 'open',
            'sender_role' => null,
        ]);
        $report->roles()->sync(Role::where('name', 'main_admin')->first()->id);

        $recipients = Employee::whereHas('account', function ($query) {
            $query->whereHas('roles', function ($query) {
                $query->where('name', 'main_admin');
            });
        })->get();


        foreach ($recipients as $recipient) {

            $fcm_tokens = $recipient->fcm_tokens()->pluck('fcm_token')->toArray();

            foreach ($fcm_tokens as $fcm) {
                $this->send_notification(
                    $fcm,
                    $request->type,
                    $request->content,
                    'report_page,'.$report->id,
                    'dashboard'
                );
            }

            $recipient->notifications()->create([
                'employee_id' => $recipient->id,
                'type' => "report_page,".$report->id, // 1 is to redirect to the orders page
                'title' => ["ar"=>'User Feedback',"en"=>"User Feedback"],
                'body' => ["ar"=>$request->content,"en"=>$request->content]
            ]);
        }



        return $report;
    }

    public function createGeneralReport($request, $user)//si
    {
        $report = Report::create([
            'employee_id' => null,
            'user_id' => $user->id,
            'order_id' => null,
            'reply' => null,
            'reply_by' => null,
            'type' => $request->type,
            'rate' => null,
            'from' => null,
            'content' => $request->content,
            'status' => 'open',
            'sender_role' => null,
        ]);

        $report->roles()->sync(Role::where('name', 'main_admin')->first()->id);
        $recipients = Employee::whereHas('account', function ($query) {
            $query->whereHas('roles', function ($query) {
                $query->where('name', 'main_admin');
            });
        })->get();

        foreach ($recipients as $recipient) {

            $fcm_tokens = $recipient->fcm_tokens()->pluck('fcm_token')->toArray();

            foreach ($fcm_tokens as $fcm) {
                $this->send_notification(
                    $fcm,
                    $request->type,
                    $request->content,
                    'report_page,'.$report->id,
                    'dashboard'
                );
            }

            $recipient->notifications()->create([
                'employee_id' => $recipient->id,
                'type' => "report_page", // 1 is to redirect to the orders page
                'title' => ["ar"=>$request->type,"en"=>$request->type],
                'body' => ["ar"=>$request->content,"en"=>$request->content]
            ]);
        }

        return $report;
    }

    public function getReport() //si
    {
        try {
            $employee = auth('api-employees')->user();
            $employee_account = $employee->account;
            if (!$employee_account) {
                throw new Exception('Employee does not have any account');
            }
            $employee_role = $employee_account->roles->first();
            if (!$employee_role) {
                throw new Exception('Employee does not have any role');
            }
            $report_id = request('report_id');
            $report = Report::with([
                'employee' => function ($query) {
                    $query->select(['id', 'first_name', 'last_name', 'email', 'phone', 'account_id']);
                },
                'roles' => function ($query) {
                    $query->select('name');
                },
                'employee.account.roles' => function ($query) {
                    $query->select('name');
                },
                'replyEmployee' => function ($query) {
                    $query->select(['id', 'first_name', 'last_name']);
                }
                //filter results to get only the report if the current authenticated user has the authority
            ])->whereHas('roles', function ($query) use ($employee_role) {
                $query->where('name', $employee_role->name);
            })->whereNotNull('employee_id')
                ->find($report_id);
            if (!$report) {
                throw new Exception('Report was not found Or you do not have the permission to see it');
            }
            $i = Inventory::find($report->inventory_id);
            $name = $i->name ?? null;
            $report->from = $name;
            return $report;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getUserReport() //si
    {
        try {
            $employee = auth('api-employees')->user();
            $employee_account = $employee->account;

            if (!$employee_account) {
                throw new Exception('Employee does not have any account');
            }

            $employee_role = $employee_account->roles->first();

            if (!$employee_role) {
                throw new Exception('Employee does not have any role');
            }

            $report_id = request('report_id');
            $report = Report::with([
                'user' => function ($query) {
                    $query->select(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at']);
                },
                'order' => function ($query) {
                    $query->select(['id', 'user_id', 'address_id', 'employee_id', 'invoice_number', 'type', 'created_at']);
                },
                'order.address' => function ($query) {
                    $query->select(['id', 'city', 'neighborhood', 'street']);
                },
                'order.employee' => function ($query) {
                    $query->select(['id', 'first_name', 'last_name', 'phone', 'shift_id']);
                },
                'order.employee.shift' => function ($query) {
                    $query->select(['id', 'name']);
                },
                'replyEmployee' => function ($query) {
                    $query->select(['id', 'first_name', 'last_name']);
                }
            ])
                ->whereHas('roles', function ($query) use ($employee_role) {
                    $query->where('name', $employee_role->name);
                })->whereNotNull('user_id')
                ->find($report_id);

            if (!$report) {
                throw new Exception('Report was not found');
            }

            return $report;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function replyToReport() //si
    {
        try {
            $employee = auth('api-employees')->user();
            $flag = false;

            if (!$employee) {
                throw new Exception('Employee does not exist');
            }

            $employee_account = $employee->account;

            if (!$employee_account) {
                throw new Exception('Employee does not have any account');
            }

            $employee_role = $employee_account->roles->first();

            if (!$employee_role) {
                throw new Exception('Employee does not have any role');
            }

            $roles = $employee->account->roles->pluck('id')->toArray();
            $report_id = request('report_id');
            $report = Report::with('roles')->find($report_id);
            $duration = $report->created_at->locale('en')->diffForHumans(now());

            if (!$report) {
                throw new Exception("Report does not exist");
            }

            if ($report->status == 'solved') {
                throw new Exception("This is closed report, You cant reply to it anymore");
            }

            foreach ($report->roles as $role) {
                if (in_array($role->id, $roles)) {
                    $flag = true;
                }
            }

            if (!$flag) {
                throw new Exception("You don't have the permission to reply to this report");
            }

            $reply = request('reply');
            $report->update([
                'reply' => $reply,
                'status' => 'solved',
                'reply_by_id' => $employee->id,
                'duration' => $duration,
            ]);
            $user = $report->user()->first();

            if ($user) {
                $title = [
                    "ar" => "تم مراجعة الشكوى",
                    "en" => "We riviewd your message"
                ];
                $fcm_tokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();
                foreach ($fcm_tokens as $fcm) {
                    $fcm_token = FcmToken::where([['fcm_token', $fcm], ['user_id', $user->id]])->first();

                    if ($fcm_token->lang == 'en') {
                        $this->send_notification(
                            $fcm,
                            'We reviewed your message',
                            'Click to view more details',
                            'Notification',
                            'flutter_app',
							null,
							$reply
                        ); // key source	
                    } else {
                        $this->send_notification(
                            $fcm,
                            'تم مراجعة الشكوى',
                            $reply,
                            'Notification',
                            'flutter_app'
                        ); // key source
                    }
                }

                $user->notifications()->create([
                    'user_id' => $user->id,
                    'type' => 'Notification', // 5 is to for reports
                    'title' => $title,
                    'body' => $reply
                ]);
            }
            return [
                'report' => $report,
                'name' => $employee['first_name'] . ' ' . $employee['last_name']
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getUserReportCards()//si
    {
        try {
            $employee = auth('api-employees')->user();

            if (!$employee) {
                return response()->error('Unauthorized', 403);
            }
            $sender_account = $employee->account;
            
            if (!$sender_account) {
                throw new Exception('Employee does not have any account');
            }
            
            $sender_role = $sender_account->roles->first();
            
            if (!$sender_role) {
                throw new Exception('Employee does not have any role');
            }
            
            $reports = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->get();
            $dateScope = request('date_scope');
            $from_date = null;
            $to_date = null;
            
            if ($dateScope == null) {
                $dateScope == 'Today';
            }

            $modelName = \App\Models\Report::class;
            $all = Report::scopeDateRange($reports, $modelName, $dateScope, $from_date, $to_date)->whereNotNull('user_id')->count();
            $closed = Report::scopeDateRange($reports, $modelName, $dateScope, $from_date, $to_date)->whereNotNull('user_id')->where('status', 'solved')->count();
            $open = Report::scopeDateRange($reports, $modelName, $dateScope, $from_date, $to_date)->whereNotNull('user_id')->where('status', 'open')->count();

            return [
                'all' => $all,
                'closed' => $closed,
                'open' => $open
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getEmployeeReportCards() //si
    {
        try {
            $employee = auth('api-employees')->user();
            //$employee = Employee::find(8);

            if (!$employee) {
                return response()->error('Unauthorized', 403);
            }
            $sender_account = $employee->account;
            if (!$sender_account) {
                throw new Exception('Employee does not have any account');
            }
            $sender_role = $sender_account->roles->first();
            if (!$sender_role) {
                throw new Exception('Employee does not have any role');
            }

            $dateScope = request('date_scope');

            if ($dateScope == null) {
                $dateScope = 'Today';
            }

            if ($dateScope == 'last_year') {
                $all = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->whereDate('created_at', '>=', now()->startOfDay())->count();
                $closed = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->where('status', 'solved')->whereDate('created_at', '>=', now()->startOfYear())->count();
                $open = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->where('status', 'open')->where('status', 'solved')->whereDate('created_at', '>=', now()->startOfYear())->count();
            } elseif ($dateScope == 'last_month') {
                $all = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->whereDate('created_at', '>=', now()->startOfDay())->count();
                $closed = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->where('status', 'solved')->whereDate('created_at', '>=', now()->startOfMonth())->count();
                $open = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->where('status', 'open')->where('status', 'solved')->whereDate('created_at', '>=', now()->startOfMonth())->count();
            } elseif ($dateScope == 'last_week') {
                $all = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->whereDate('created_at', '>=', now()->startOfDay())->count();
                $closed = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->where('status', 'solved')->whereDate('created_at', '>=', now()->startOfWeek())->count();
                $open = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->where('status', 'open')->where('status', 'solved')->whereDate('created_at', '>=', now()->startOfWeek())->count();
            } else {
                $all = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->whereDate('created_at', '>=', now()->startOfDay())->count();
                $closed = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->where('status', 'solved')->whereDate('created_at', '>=', now()->startOfDay())->count();
                $open = $sender_role->reports()->with('employee')->whereNotNull('employee_id')->where('status', 'open')->where('status', 'solved')->whereDate('created_at', '>=', now()->startOfDay())->count();
            }

            return [
                'all' => $all,
                'closed' => $closed,
                'open' => $open
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    protected function applyFilters($query, array $filters)
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

    protected function filterBySearch($query, $filter_data)
    {
        // return $query->whereLike('shipment_name', $filter_data['search']);
        $search = $filter_data['search'];
        // dd($search);
        return $query->where(function ($query) use ($search) {
            $query->where('content', 'LIKE', '%' . $search . '%')
                ->orWhereHas('user', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('first_name', 'LIKE', '%' . $search . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                            ->orWhere('phone', 'LIKE', '%' . $search . '%');
                    });
                })
                ->orWhereHas('employee', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('first_name', 'LIKE', '%' . $search . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                            ->orWhere('phone', 'LIKE', '%' . $search . '%');
                    });
                });
        });
    }

    protected function filterByDate($query, $filter_data)
    {
        $date_min = $filter_data['date_min'] ?? 0;
        $date_max = $filter_data['date_max'] ?? date('Y-m-d');

        return $query->whereBetween('created_at', [$date_min, $date_max]);
    }

    protected function filterByStatus($query, $filter_data)
    {
        $status = $filter_data['status'];
        return $query->where('status', $status);
    }

    protected function filterByFrom($query, $filter_data)
    {
        $from = $filter_data['from'];
        return $query->where('inventory_id', $from);
    }

    protected function filterByType($query, $filter_data)
    {
        // return "ho";
        $type = $filter_data['type'];
        // return $type;
        return $query->where('type', $type);
        // return $query->where('type', $type);
    }
}
