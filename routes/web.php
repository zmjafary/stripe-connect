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

Route::get('/', function () {
    return view('welcome');
});


Route::get('seller/{id}', 'SellerController@showProfile')->name('seller.profile');
Route::get('stripe/{id}', 'SellerController@redirectToStripe')->name('redirect.stripe');
Route::get('connect/{token}', 'SellerController@saveStripeAccount')->name('save.stripe');
Route::post('charge/{id}', 'SellerController@purchase')->name('complete.purchase');
