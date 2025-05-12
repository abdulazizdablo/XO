<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Users\v1\PaymentController;
use App\Http\Controllers\ContactController;

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
        require __DIR__ . '/api/v1/stock_level.php';
        require __DIR__ . '/api/v1/sub_category.php';
        require __DIR__ . '/api/v1/coupon.php';
        require __DIR__ . '/api/v1/offer.php';
        require __DIR__ . '/api/v1/section.php';
        require __DIR__ . '/api/v1/pricing.php';
        require __DIR__ . '/api/v1/review.php';
        require __DIR__ . '/api/v1/order.php';
        require __DIR__ . '/api/v1/employee.php';
        require __DIR__ . '/api/v1/dashboard.php';
        require __DIR__ . '/api/v1/discount.php';
        require __DIR__ . '/api/v1/size.php';
        require __DIR__ . '/api/v1/color.php';
        require __DIR__ . '/api/v1/setting.php';
        require __DIR__ . '/api/v1/size_guides.php';
        require __DIR__ . '/api/v1/report.php';
        require __DIR__ . '/api/v1/delivery.php';
        require __DIR__ . '/api/v1/syriatel.php';
        require __DIR__ . '/api/v1/mtn.php';
        require __DIR__ . '/api/v1/transaction.php';
    }
);

Route::post('/contact', [ContactController::class, 'sendContact']);

Route::post('/callback_url', [PaymentController::class, 'handleCallback']);
Route::post('/replace_callback_url', [PaymentController::class, 'handleReplaceCallback']);
Route::post('/gift_callback_url', [PaymentController::class, 'handleGiftCallback']);


Route::post('/home', [HomeController::class, 'home']);
Route::post('/new-mobile-version', [HomeController::class, 'newMobileVersion']);
Route::post('/update-deployment-status', [HomeController::class, 'updateDeploymentStatus']);
