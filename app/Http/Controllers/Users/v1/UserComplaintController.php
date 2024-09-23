<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\UserComplaint;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use App\Services\UserComplaintService;

class UserComplaintController extends Controller
{




    public function __construct(
        protected UserComplaintService $userComplaintService
        )
    {
      
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user=auth('sanctum')->user();
        $user_id  =$user->id;
        $user_complaints = $this->userComplaintService->getUserComplaintsByUser($user_id);

        return response()->success(
            $user_complaints
        , Response::HTTP_OK);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $user=auth('sanctum')->user();
            $user_id  =$user->id;
            $validate = Validator::make($request->all(),
                [
                    'title' => 'required|string|max:255',
                    'details' => '|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                          $validate->errors()

                    , Response::HTTP_BAD_REQUEST
                );
            }

            $validated_data = $validate->validated();

            $feedbackService = $this->userComplaintService->createUserComplaint($validated_data, $user_id);

            return response()->success(
                [
                    'message'=>'User Complaint Is Created',
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_BAD_REQUEST
            );
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserComplaint  $userComplaint
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $user_complaint_id = request('user_complaint_id');
            $user_complaint = $this->userComplaintService->getUserComplaint($user_complaint_id);

            return response()->success(
                $user_complaint,
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
               $e->getMessage(),
                Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserComplaint  $userComplaint
     * @return \Illuminate\Http\Response
     */
    public function edit(UserComplaint $userComplaint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserComplaint  $userComplaint
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserComplaint $userComplaint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserComplaint  $userComplaint
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserComplaint $userComplaint)
    {
        //
    }
}
