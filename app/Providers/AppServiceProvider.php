<?php

namespace App\Providers;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use App\Observers\SettingObserver;
use App\Models\AppSetting;
use App\Observers\AppSettingObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(200);

        ProductCollection::withoutWrapping();
        ProductResource::withoutWrapping();

        Response::macro('success', function($data, $status_code){
            return response()->json([
                'success' => true,
                'data' => $data
            ], $status_code);
        });

        Response::macro('error', function($error, $status_code){
            return response()->json([
                'success' => false,
                'error' => $error
            ], $status_code);
        });

        Setting::observe(SettingObserver::class);
        AppSetting::observe(AppSettingObserver::class);
    }
}
