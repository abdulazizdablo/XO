<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Users\v1\NewPasswordController;
use App\Http\Controllers\Users\v1\PaymentController;
use App\Http\Controllers\Users\v1\UserLastViewedController;
use App\Http\Controllers\Users\v1\VerifyEmailController;
use Illuminate\Support\Facades\Artisan;
use App\Models\Group;
use App\Models\SizeGuide;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/ 

Route::group(
    [
        'middleware' => [
            'Locale',
            'Location'
        ],
    ],
    function () {
        require __DIR__ . '/api/v1/user.php';
        require __DIR__ . '/api/v1/auth.php';
        require __DIR__ . '/api/v1/contact.php';
        require __DIR__ . '/api/v1/app_setting.php';
        require __DIR__ . '/api/v1/address.php';
        require __DIR__ . '/api/v1/exchange.php';
        require __DIR__ . '/api/v1/refund.php';
        require __DIR__ . '/api/v1/admin.php';
        require __DIR__ . '/api/v1/branch.php';
        require __DIR__ . '/api/v1/city.php';
        require __DIR__ . '/api/v1/product.php';
        require __DIR__ . '/api/v1/cargo_request.php';
        require __DIR__ . '/api/v1/cargo_shipment.php';
        require __DIR__ . '/api/v1/product_variation.php';
        require __DIR__ . '/api/v1/group.php';
        require __DIR__ . '/api/v1/group_offer.php';
        require __DIR__ . '/api/v1/group_discount.php';
        require __DIR__ . '/api/v1/category.php';
        require __DIR__ . '/api/v1/inventory.php';
        require __DIR__ . '/api/v1/favourite.php';
        require __DIR__ . '/api/v1/package.php';
        require __DIR__ . '/api/v1/stock_level.php';
        require __DIR__ . '/api/v1/stock_movement.php';
        require __DIR__ . '/api/v1/sub_category.php';
        require __DIR__ . '/api/v1/coupon.php';
        require __DIR__ . '/api/v1/offer.php';
        require __DIR__ . '/api/v1/feedback.php';
        require __DIR__ . '/api/v1/section.php';
        require __DIR__ . '/api/v1/pricing.php';
        require __DIR__ . '/api/v1/review.php';
        require __DIR__ . '/api/v1/order.php';
        require __DIR__ . '/api/v1/employee.php';
        require __DIR__ . '/api/v1/dashboard.php';
        require __DIR__ . '/api/v1/discount.php';
        require __DIR__ . '/api/v1/comment.php';
        require __DIR__ . '/api/v1/variation.php';
        require __DIR__ . '/api/v1/size.php';
        require __DIR__ . '/api/v1/color.php';
        require __DIR__ . '/api/v1/setting.php';
        require __DIR__ . '/api/v1/size_guides.php';
        require __DIR__ . '/api/v1/user_complaint.php';
        require __DIR__ . '/api/v1/report.php';
        require __DIR__ . '/api/v1/delivery.php';
        require __DIR__ . '/api/v1/syriatel.php';
        require __DIR__ . '/api/v1/mtn.php';
        require __DIR__ . '/api/v1/transaction.php';


        // require __DIR__ . '/api/v1/auth.php';
    }
);

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
    return "Cache cleared successfully";
 });
 Route::get('/migrate', function () {
    if(config("app.debug")) {
            Artisan::call('migrate:fresh --seed');

 return "Migrated successfully";
    }else{
        return "cannot migrate in production mode";
    }


});

 Route::get('/settings-seeder', function () {
    if(config("app.debug")) {
            Artisan::call('db:seed SettingSeeder');

 return "Migrated successfully";
    }else{
        return "cannot migrate in production mode";
    }


});


Route::get('discount_group', function(){
    $group = Group::find(2);
    $discounts = collect($group->discounts)->pluck('id');
    return response()->json([
        'status' => 200,
        'group_id' => $group->id,
        'data' => $discounts
    ]);
});

Route::get('product_group', function(){
    $group = Group::find(3);
    $products = collect($group->products)->pluck('id');
    return response()->json([
        'status' => 200,
        'group_id' => $group->id,
        'data' => $products
    ]);
});

Route::get('detach_discount_group', function(){
    $group = Group::find(3);
    $group_before = collect($group->discounts)->pluck('id');
    $discount_id = request('discount_id');
    $group->discounts()->detach($discount_id);
    $group = Group::find(3);
    $group_after = collect($group->discounts)->pluck('id');
    return response()->json([
        'status' => 200,
        'group_id' => $group->id,
        'data_before' => $group_before,
        'data_after' => $group_after,
    ]);
});

Route::get('detach_product_group', function(){
    $group = Group::find(3);
    $group_before = collect($group->products)->pluck('id');
    $product_id = request('product_id');
    $group->products()->detach($product_id);
    $group = Group::find(3);
    $group_after = collect($group->products)->pluck('id');
    return response()->json([
        'status' => 200,
        'group_id' => $group->id,
        'data_before' => $group_before,
        'data_after' => $group_after,
    ]);
});

Route::get('attach_product_group', function(){
    $group = Group::find(3);
    $group_before = collect($group->products)->pluck('id');
    $product_id = request('product_id');
    $group->products()->attach($product_id);
    $group = Group::find(3);
    $group_after = collect($group->products)->pluck('id');
    return response()->json([
        'status' => 200,
        'data_before' => $group_before,
        'data_after' => $group_after,
    ]);
});

Route::get('attach_discount_group', function(){
    $group = Group::find(3);
    $group_before = collect($group->discounts)->pluck('id');
    $discount_id = request('discount_id');
    $group->discounts()->attach($discount_id);
    $group = Group::find(3);
    $group_after = collect($group->discounts)->pluck('id');
    return response()->json([
        'status' => 200,
        'data_before' => $group_before,
        'data_after' => $group_after,
    ]);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Verify email
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Resend link to verify email
Route::post('/email/verify/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    // return back()->with('message', 'Verification link sent!');
    return response()->json(["message" => 'Verification link sent!'], 200);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');


Route::post('/forgot-password', [NewPasswordController::class, 'forgotPassword']);
Route::get('/reset-password',  [NewPasswordController::class, 'reset']);
Route::post('/change-password',  [NewPasswordController::class, 'change']);

Route::post('/callback_url', [PaymentController::class, 'handleCallback']);
Route::post('/replace_callback_url', [PaymentController::class, 'handleReplaceCallback']);
Route::post('/gift_callback_url', [PaymentController::class, 'handleGiftCallback']);


Route::post('/home', [HomeController::class, 'home']);
Route::post('/new-mobile-version', [HomeController::class, 'newMobileVersion']);
Route::post('/update-deployment-status', [HomeController::class, 'updateDeploymentStatus']);
