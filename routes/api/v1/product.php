<?php

use App\Http\Controllers\Users\v1\FavouriteController;
use App\Http\Controllers\Dashboard\ProductController as AdminProductController;
use App\Http\Controllers\Users\v1\NotifyController;
use App\Http\Controllers\Users\v1\ProductController as UserProductController;
use Illuminate\Support\Facades\Route;


Route::group([
	'prefix' => '/dashboard/products',
	'as' => 'dashboard.products.',
	'middleware' => 'CheckIfEmployee'
],function(){
	Route::get('', [AdminProductController::class, 'index']);
	Route::get('show', [AdminProductController::class, 'show']); //si
	Route::get('show/counts', [AdminProductController::class, 'showCounts']);
	Route::get('info', [AdminProductController::class, 'info']); //si
	Route::get('show/reviews', [AdminProductController::class, 'showReviews']); //si
	Route::get('show/orders', [AdminProductController::class, 'showOrders']); //si
	Route::get('show/stocks', [AdminProductController::class, 'showStocks']); //si
	Route::post('import', [AdminProductController::class, 'import']);//si
	Route::post('change/visibility', [AdminProductController::class, 'changeVisibility']); //si
	Route::post('attach', [AdminProductController::class, 'attach']); //si
	Route::get('search', [AdminProductController::class, 'searchProduct']);
	Route::get('favourite', [AdminProductController::class, 'getFavourite']);//si
	Route::get('flash_sales', [AdminProductController::class, 'getFlashSales']);
	Route::get('check/item', [AdminProductController::class, 'checkItemoNo']); //si
	Route::post('store', [AdminProductController::class, 'store']); //si
	Route::post('update', [AdminProductController::class, 'update']); //si
	Route::post('photos/store', [AdminProductController::class, 'storePhotos']); //si
	Route::post('photos/update', [AdminProductController::class, 'updatePhotos']); //si
	Route::delete('photos/delete', [AdminProductController::class, 'deletePhotos']); //si
	Route::post('photos/update-main', [AdminProductController::class, 'updateMainPhoto']);
	Route::post('delete', [AdminProductController::class, 'destroy']); //si
	Route::delete('deleteMany', [AdminProductController::class, 'deleteMany']); //si
	Route::delete('', [AdminProductController::class, 'forceDelete']);
	Route::get('export', [AdminProductController::class, 'export']);//si	

});

Route::group([
	'prefix' => '/v1/products',
	'as' => 'products.',
], function () {
	Route::get('', [UserProductController::class, 'index']); //si
	Route::get('show', [UserProductController::class, 'show']); //si
	Route::get('test', [UserProductController::class, 'test']);
	Route::get('bySku_code', [UserProductController::class, 'getProductBySku_code']);
	Route::get('byItem_no', [UserProductController::class, 'getProductByItem_no']);//si
	Route::get('favourite', [UserProductController::class, 'getFavourite']);//si
	Route::post('remove-favourite', [UserProductController::class, 'removeFavourite']);
	Route::get('reviews/{product}', [UserProductController::class, 'productReviews']);
	Route::get('notified', [UserProductController::class, 'getNotified']);
	Route::get('by_category', [UserProductController::class, 'getProductsByCategory']); //si
	Route::get('by_category-f', [UserProductController::class, 'getProductsByCategoryFlutter']);//si
	Route::put('add_to_favorite', [FavouriteController::class, 'store']); //si
	Route::post('notify_me', [NotifyController::class, 'store']);//si
	Route::get('get-notifies', [NotifyController::class, 'getUserNotifies']);//si
	Route::get('flash_sales', [UserProductController::class, 'getFlashSales']);//si
	Route::get('fuzzySearch', [UserProductController::class, 'fuzzySearch']);//si
	Route::get('searchWebsite', [UserProductController::class, 'SearchWebsite']);
	Route::get('similar_products', [UserProductController::class, 'similar_products']);//si
	Route::get('recommendation_products', [UserProductController::class, 'recommendation_products']); //si
	Route::get('by_group', [UserProductController::class, 'getGroupProductsBySlug']);//si
	Route::post('addLastViewedProduct/{id}', [UserProductController::class, 'addLastViewedProduct']);
	Route::get('showLastViewedProducts', [UserProductController::class, 'showLastViewedProducts']);//si
	Route::get('newIn', [UserProductController::class, 'newIn']);//si
	Route::get('top-product', [UserProductController::class, 'top_product']);//si
	Route::get('newly-added', [UserProductController::class, 'newlyAdded']);
	Route::get('home-section-products', [UserProductController::class, 'homeSectionProducts']);//si
	Route::get('mock-http', [UserProductController::class, 'mockHttp']);//for development
	Route::get('notify-favourite', [UserProductController::class, 'getUserNofitiedFav']);
	Route::get('products-by-group', [UserProductController::class, 'productsByGroup']);
	Route::post('unnotify', [UserProductController::class, 'unnotify']);//si
	Route::get('export', [AdminProductController::class, 'export']);
	Route::get('adjust', [UserProductController::class, 'adjust']);
});
