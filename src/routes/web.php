<?php

use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

Route::group(['namespace' => 'Dorcas\ModulesAdmin\Http\Controllers', 'prefix' => 'admin', 'middleware' => ['web','auth']], function() {
    Route::get('/admin-main', 'ModulesAdminController@index')->name('admin-main');
});

Route::group(['namespace' => 'Dorcas\ModulesAdmin\Http\Controllers', 'prefix' => 'admin/endpoint', 'middleware' => ['dorcasBridgeAdmin']], function() {
    Route::get('/test', 'ModulesAdminEndpointController@test');
});


?>