<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Services\SettingService;


class SettingController extends Controller
{



    public function __construct(
        protected SettingService $settingService
    ) {
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
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function show(Setting $setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function edit(Setting $setting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Setting $setting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Setting $setting)
    {

        //
    }
    // loginNotification , BanUserNotification //
    public function updateNotifications(Setting $setting)
    {
        $key = request('key');
        $title = request('title');
        $body = request('body');
        $loginNotification = $this->settingService->updateNotifications($key, $title, $body);

        return response()->json($loginNotification, 200);
    }

    public function updatelinks(Setting $setting)
    {
        $key = request('key');
        $phone = request('phone');
        $helpdesk = request('helpdesk');
        $facebook = request('facebook');
        $instagram = request('instagram');
        $twitter = request('twitter');
        $whatsapp = request('whatsapp');
        $email = request('email');
        $google_play = request('google_play');
        $app_store = request('app_store');
        $updatelinks = $this->settingService->updatelinks($key, $phone, $helpdesk, $facebook, $instagram, $twitter, $whatsapp, $email, $google_play, $app_store);

        return response()->json($updatelinks, 200);
    }




    public function getaboutus()
    {

        $info = request('info');
        if ($info != "null") {
            $about = Setting::select("value")->where('key', 'about_us')->first();
        }
        return response()->json($about, 200);
    }
}
