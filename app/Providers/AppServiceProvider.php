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
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Events\RouteMatched;

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

		/////////////////////////////this part is to log all urls in log file/////////////////////////
		///*
		$this->app['events']->listen(RouteMatched::class, function ($event) {
			$routeUri = $event->route->uri(); // الحصول على نمط رابط الراوت
			$controllerAction = $event->route->getActionName();

			$requestBody = request()->all();
		    $bearerToken = request()->bearerToken();

			// أو الطريقة الثانية: إذا كان الحدث يحتوي على الخاصية request
			// $requestBody = $event->request->all();
			Log::info('/////////////////////////////////////////////////////');
			Log::info('route_url: '.$routeUri);
			Log::info('controller_action: '.$controllerAction);
			Log::info('Bearer Token: '.$bearerToken);
			Log::info('request_body: ', [$requestBody]);
			/*Log::info('Route matched', [
				'route_url'          => $routeUri,
				'controller_action'  => $controllerAction,
				'request_body'       => $requestBody,
			]);*/
			Log::info('/////////////////////////////////////////////////////');
		});

		//*/
		///////////////////////////////////////////////////////////////////////////////////////////////
	}
}
