<?php

Route::group([

    'middleware' => 'api',

], function () {

    //User login and factor authentication
    Route::post('login', 'AuthController@login');
    Route::patch('resendSms', 'AuthController@resendSms');
    Route::post('factorAuthentication', 'FactorAuthenticationController@factorAuthentication');

    //user sign up and verify account
    Route::post('signup', 'AuthController@signup');
    Route::post('verificationAccount', 'VerificationAccountController@verificationAccount');

    //logout
    Route::post('logout', 'AuthController@logout');

    //user reset password
    Route::post('sendPasswordResetLink', 'ResetPasswordController@sendEmail');
    Route::post('resetPassword', 'ChangePasswordController@process');

    Route::post('me', 'AuthController@me');
    Route::post('refresh', 'AuthController@refresh');

    //video
    Route::get('video/{id}', 'VideoController@showVideos');
    Route::post('videoCreate', 'VideoController@create');
    Route::get('videoChangeStatus/{id}', 'VideoController@videoChangeStatus');
    Route::get('videoEdit/{id}', 'VideoController@videoEdit');
    Route::patch('videoUpdate', 'VideoController@videoUpdate');
    Route::delete('video/{id}', 'VideoController@videoDelete');
});
