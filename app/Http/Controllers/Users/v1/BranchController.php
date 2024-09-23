<?php

namespace App\Http\Controllers\Users\v1;

use App\Models\Branch;
use App\Http\Controllers\Controller;
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $city_id = request('city_id');
        $branches = $this->branchService->getBranchesByCityId($city_id);

        return response()->success(
            $branches,
            Response::HTTP_OK
        );
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

            return response()->success(
                $branch,
                Response::HTTP_FOUND
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
               $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }


}
