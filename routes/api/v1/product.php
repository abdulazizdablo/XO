<?php

use App\Http\Controllers\Users\v1\FavouriteController;
use App\Http\Controllers\Dashboard\ProductController as AdminProductController;
use App\Http\Controllers\Users\v1\NotifyController;
use App\Http\Controllers\Users\v1\ProductController as UserProductController;
use App\Http\Controllers\Users\v1\UserLastViewedController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/products',
    'as' => 'dashboard.products.',
], function () {
    Route::get('', [AdminProductController::class, 'index']);
    Route::get('show', [AdminProductController::class, 'show']);
    Route::get('show/counts', [AdminProductController::class, 'showCounts']);
    Route::get('info', [AdminProductController::class, 'info']);
    Route::get('show/reviews', [AdminProductController::class, 'showReviews']);
    Route::get('show/orders', [AdminProductController::class, 'showOrders']);
    Route::get('show/stocks', [AdminProductController::class, 'showStocks']);
    Route::get('export', [AdminProductController::class, 'export']);
    Route::post('import', [AdminProductController::class, 'import']);
    Route::post('change/visibility', [AdminProductController::class, 'changeVisibility']);
    Route::post('attach', [AdminProductController::class, 'attach']);
    Route::get('search', [AdminProductController::class, 'searchProduct']);
    Route::get('favourite', [AdminProductController::class, 'getFavourite']);
    Route::get('flash_sales', [AdminProductController::class, 'getFlashSales']);
    Route::get('check/item', [AdminProductController::class, 'checkItemoNo']);
    Route::post('store', [AdminProductController::class, 'store']);
    Route::post('update', [AdminProductController::class, 'update']);
    Route::post('photos/store', [AdminProductController::class, 'storePhotos']);
    Route::post('photos/update', [AdminProductController::class, 'updatePhotos']);
    Route::delete('photos/delete', [AdminProductController::class, 'deletePhotos']);
    Route::post('photos/update-main', [AdminProductController::class, 'updateMainPhoto']);


    
    Route::post('update', [AdminProductController::class, 'update']);
    Route::post('delete', [AdminProductController::class, 'destroy']);
    Route::delete('deleteMany', [AdminProductController::class, 'deleteMany']);
    Route::delete('', [AdminProductController::class, 'forceDelete']);
});

Route::group([
    'prefix' => '/v1/products',
    'as' => 'products.',
], function () {
    Route::get('', [UserProductController::class, 'index']);
    Route::get('show', [UserProductController::class, 'show']);
    Route::get('test', [UserProductController::class, 'test']);//->middleware(['CheckIsSuperAdmin']);
    Route::get('bySku_code', [UserProductController::class, 'getProductBySku_code']);
    Route::get('byItem_no', [UserProductController::class, 'getProductByItem_no']);
    Route::get('search', [UserProductController::class, 'searchProduct']);
    Route::get('favourite', [UserProductController::class, 'getFavourite']);
    Route::post('remove-favourite', [UserProductController::class, 'removeFavourite']);
    Route::get('reviews/{product}', [UserProductController::class, 'productReviews']);
     Route::get('notified', [UserProductController::class, 'getNotified']);
    Route::get('by_category', [UserProductController::class, 'getProductsByCategory']);
    Route::get('by_category-f', [UserProductController::class, 'getProductsByCategoryFlutter']);
    Route::put('add_to_favorite', [FavouriteController::class, 'store'])->name('add.favourite');
    Route::post('notify_me', [NotifyController::class, 'store']);
    Route::get('get-notifies', [NotifyController::class, 'getUserNotifies']);
    Route::get('flash_sales', [UserProductController::class, 'getFlashSales']);
    Route::get('fuzzySearch', [UserProductController::class, 'fuzzySearch']);
    Route::get('searchWebsite', [UserProductController::class, 'SearchWebsite']);
    Route::get('similar_products', [UserProductController::class, 'similar_products']);
    Route::get('recommendation_products', [UserProductController::class, 'recommendation_products']);
    Route::get('by_group', [UserProductController::class, 'getGroupProductsBySlug']);
    Route::post('addLastViewedProduct/{id}',[UserProductController::class, 'addLastViewedProduct']);
    Route::get('showLastViewedProducts',[UserProductController::class, 'showLastViewedProducts']);
    Route::get('newIn',[UserProductController::class, 'newIn']);
    Route::get('top-product',[UserProductController::class, 'top_product']);
    Route::get('newly-added', [UserProductController::class, 'newlyAdded']);
    Route::get('home-section-products', [UserProductController::class, 'homeSectionProducts']);
    Route::get('mock-http', [UserProductController::class, 'mockHttp']);
    Route::get('notify-favourite', [UserProductController::class, 'getUserNofitiedFav']);
    Route::get('products-by-group', [UserProductController::class, 'productsByGroup']);
    Route::post('unnotify', [UserProductController::class, 'unnotify']);
Route::get('export', [AdminProductController::class, 'export']);
    Route::get('adjust', [UserProductController::class, 'adjust']);

});
