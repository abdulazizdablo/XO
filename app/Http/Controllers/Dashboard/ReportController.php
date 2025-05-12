<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
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
    public function index(Request $request)//si
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

            $reports = $this->reportService->getAllReports($filter_data);

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
    public function store(Request $request) //si
    {
        try {
            $employee = auth('api-employees')->user();

            if (!$employee) {
                throw new Exception('Employee does not exist');
            }

            $validated = Validator::make(
                $request->all(),
                [
                    'content' => 'required|max:255',
                    'type' => 'sometimes|max:255',
                ]
            );

            if ($validated->fails()) {
                return response()->error(
                    $validated->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $report = $this->reportService->createReport($request, $employee);


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


    public function getCards() //si
    {
        try {
            $cards = $this->reportService->getEmployeeReportCards();
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
    public function show() //si
    {
        try {
            $report = $this->reportService->getReport();
            return response()->success($report, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function replyToReport()//si
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
