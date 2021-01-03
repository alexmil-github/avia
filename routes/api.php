<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('/register', 'App\Http\Controllers\AuthController@registration');
Route::post('/login', 'App\Http\Controllers\AuthController@login');
Route::get('/airport', 'App\Http\Controllers\AirportController@search');
Route::get('/flight', 'App\Http\Controllers\FlightController@search');
Route::post('/booking', 'App\Http\Controllers\BookingController@booking');