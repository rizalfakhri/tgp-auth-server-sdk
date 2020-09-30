<?php

Route::group(['prefix' => 'auth', 'middleware' => 'web'], function() {

    Route::group(['middleware' => 'auth'], function() {
        Route::get('deauthorize', 'TGP\AuthServer\Http\Controllers\DeauthorizeController@deauthorize');
    });

    Route::group(['middleware' => 'guest'], function() {
        Route::get('authorize', 'TGP\AuthServer\Http\Controllers\AuthorizeController@redirect')->name('login');
        Route::get('callback', 'TGP\AuthServer\Http\Controllers\AuthorizeController@callback');
    });


});

Route::group(['prefix' => 'auth', 'middleware' => 'api'], function() {
    Route::post('token', 'TGP\AuthServer\Http\Controllers\AccessTokenController@exchangeAuthorizationCode');
    Route::post('token/refresh', 'TGP\AuthServer\Http\Controllers\AccessTokenController@refreshAccessToken');
});
