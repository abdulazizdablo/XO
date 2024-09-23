<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use App\Services\ReportService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Exception;

class ReportController extends Controller


{

    public function __construct(protected ReportService $reportService)
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $filter_data = $request->only([
                'date_min',
                'date_max',
                'search',
                'type',
                'status',
                'from'
            ]);

            $reports = $this->reportService->getAllUserReports($filter_data);

            return response()->success(
                $reports,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createOrderReport(Request $request)
    {
        try {
        $user = auth('sanctum')->user();
        // $user = User::find(1);
        if(!$user){
            throw new Exception('User does not exist');
        }
        $order_id = request('order_id');
        $order = Order::findOrFail($order_id);
     
        if($order->user_id != $user->id){
            throw new Exception('This order belongs to another user');
        }
        
            $validated = Validator::make(
                $request->all(),
                [
                    'content' => 'required|max:255',
                    'rate' => 'sometimes| max:6'
                ]
            );

            if ($validated->fails()) {
                return response()->error(
                    $validated->errors(),
                    422
                );
            }

            $report = $this->reportService->createOrderReport($request, $user, $order);


            return response()->success(
                [$report],
                Response::HTTP_CREATED
            );
        } catch (\Exception $th) {
            return response()->json(
                [
                    'error' => $th->getMessage(),
                ]
            );
        }
    }

    public function createGeneralReport(Request $request)
    {
        $user = auth('sanctum')->user();
        //$user = User::find(1);
        if(!$user){
            throw new Exception('User does not exist');
        }
        try {
            $validated = Validator::make(
                $request->all(),
                [
                    'content' => 'required|max:255',
                    'type' => 'sometimes| max:255'
                ]
            );
            if ($validated->fails()) {
                return response()->error(
                    $validated->errors(),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $report = $this->reportService->createGeneralReport($request, $user);


            return response()->success(
                [$report],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'error' => $th->getMessage(),
                ]
            );
        }
    }


    public function getCards()
    {
        try {
            $cards = $this->reportService->getUserReportCards();
            return response()->success($cards, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $report = $this->reportService->getUserReport();
            return response()->success($report, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function replyToReport()
    {
        try {
            $reply = $this->reportService->replyToReport();
            return response()->success($reply, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
