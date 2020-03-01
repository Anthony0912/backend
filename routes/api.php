<?php

Route::group([

    'middleware' => 'api',

], function () {

    //User
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('sendPasswordResetLink', 'ResetPasswordController@sendEmail');
    Route::post('resetPassword', 'ChangePasswordController@process');

    //video
    Route::get('video', 'VideoController@showVideos');
    Route::post('video', 'VideoController@create');
    Route::get('video/{id}', 'VideoController@formVideoEdit');
    Route::put('video/{id}', 'VideoController@update');
    Route::delete('video/{id}', 'VideoController@delete');
    

});
