<?php

use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


// membuat Route groupe dg prefix v1(u/ maintenance) yg sdh otomatis mendapatkan path api itu sendiri
Route::group(['prefix'  =>  'v1', 'middleware' => 'cors'], function () {


    //route u/ keperluan meeting restful
    Route::resource('meeting', 'MeetingController', [
        'except' =>     ['create', 'edit']
    ]); //membuat route dg mtod resource(resful) dg path meeting n pakai MeetingController dg pengecualian aksi create dan edit karna api(dg postman tdk form)

    //membuat route untuk keperluan registration (u registrasi dan batal regis ke meeting)
    Route::resource('meeting/registration', 'RegisterController', [
        'only' =>   ['store', 'destroy']
    ]);

    //membuat rout u/ registrasi/create user 
    Route::post('/user/register', [
        'uses'  =>  'AuthController@store'
    ]);

    //membuat Route u/ Sign
    Route::post('/user/signin', [
        'uses'  =>  'AuthController@signin'
    ]);
});
