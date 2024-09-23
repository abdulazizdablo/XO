<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{

    public function __construct(
        protected  SettingService $settingService
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


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Setting  $Setting
     * @return \Illuminate\Http\Response
     */
    public function show()
    {

    }
    public function getSetting()
    {
        try {
            $setting_key = request('key');
            $setting = $this->settingService->getSetting($setting_key);

            return response()->success(
               $setting,
            Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                  $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }
    

}
