<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use Illuminate\Http\Request;
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
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'city_id' => ['required', 'integer', 'exists:cities,id'],
        ]);
        $city_id = $validatedData['city_id'];
        $branches = $this->branchService->getBranchesByCityId($city_id);

        return response()->success(
            $branches,
            Response::HTTP_OK
        );
    }

}
