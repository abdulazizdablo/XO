<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class BranchController extends Controller
{


    public function __construct(
        protected  BranchService $branchService
    ) {
    }

    public function index()
    {
        $branches = $this->branchService->getAllBranches();

        return response()->success(
            $branches,
            Response::HTTP_OK
        );
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
                $request->only('name', 'city_id'),
                [
                    'name' => 'required|string|max:255',
                    'city_id' => 'required |numeric'
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                   422
                );
            }

            $validated_data = $validate->validated();
            $branch = $this->branchService->createBranch($validated_data);

            return response()->success([
                'message' => 'Branch Is Created',
                'data' => $branch
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $branch_id = request('branch_id');
            $branch = $this->branchService->getBranch($branch_id);

            return response()->success([
                'branch' => $branch
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {

            $branch_id = request('branch_id');
            $validate = Validator::make(
                $request->only('name','city_id'),
                [
                    'name' => 'required|string|max:255',
                    'city_id' => 'required |numeric'
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                   422
                );
            }

            $validated_data = $validate->validated();

            $branch = $this->branchService->updateBranch($validated_data, $branch_id);

            return response()->success(
                [
                    'message' => 'Branch Is Updated',
                    'data' => $branch
                ],
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $branch_id = request('branch_id');
            $branch = $this->branchService->delete($branch_id);

            return response()->success(
                [
                    'message' => 'Branch Deleted Successfully',
                    'data' => $branch
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $branch_id = request('branch_id');
            $branch = $this->branchService->forceDelete($branch_id);

            return response()->success(
                [
                    'message' => 'Branch Deleted Successfully',
                    'data' => $branch
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
