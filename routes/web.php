<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


// -------------------------------> TEST


Route::get('/urls', function () {
    return response()->json([
        'url' => config('app.url'),
        'asset_url' => config('app.asset_url'),
        'public_path' => public_path(),
    ]);
});