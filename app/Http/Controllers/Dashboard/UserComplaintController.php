<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserComplaint;
use Illuminate\Http\Request;
use App\Services\UserComplaintService;


class UserComplaintController extends Controller
{
 

    public function __construct(
        protected  UserComplaintService $UserComplaintService
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
        //
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserComplaint  $userComplaint
     * @return \Illuminate\Http\Response
     */
    public function show(UserComplaint $userComplaint)
    {
        //
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
