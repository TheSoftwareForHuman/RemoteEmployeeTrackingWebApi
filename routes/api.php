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

Route::group([

    'namespace' => 'Api' ], function () {

    Route::post('/register', 'AuthController@register');

    Route::post('/login', 'AuthController@login');

    Route::get('/logout', 'AuthController@logout');

    $debug = config('app.debug');

    if ($debug) 
    {
        Route::post('/check', 'AuthController@index');
    }

    Route::post('/upload', 'AuthController@upload');
});
