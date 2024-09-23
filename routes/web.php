<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Users\v1\PaymentController;

use App\Imports\SizeGuideImport;
use Illuminate\Support\Facades\Auth;

use App\Models\SizeGuide;
use Maatwebsite\Excel\Excel as ExcelClass;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();


Route::get('/cart/shipping/payment-success', [PaymentController::class, 'handleRedirect']);

Route::get('/home', [HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::post('import', function(Request $request){
    try {
        $file = request()->file('excel');
        // if ($request->hasFile('excel')) {
        //     return "true";
        // }
        Excel::import(new SizeGuideImport, $file, null, ExcelClass::XLSX);
    } catch (\Throwable $th) {
        // return $th;
        return response()->error(
            $th->getMessage(),
            Response::HTTP_OK
        );
    }

});
