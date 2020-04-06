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
    Route::get('countries', 'AuthController@getCodeCountries');
    Route::get('settingAccount/{id}', 'AuthController@settingAccountEdit');
    Route::patch('settingAccount', 'AuthController@settingAccountUpdate');

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

    //playlist
    Route::post('playlistCreate', 'PlayListController@playlistCreate');
    Route::post('videoPlaylistCreate', 'PlayListController@videoPlaylistCreate');
    Route::get('videoPlaylist/{id_user}/{id_playlist}', 'PlayListController@videoPlaylist');
    Route::get('playlist/{id}', 'PlayListController@playlistShow');
    Route::get('playlistEdit/{id}', 'PlayListController@playlistEdit');
    Route::patch('playlistUpdate', 'PlayListController@playlistUpdate');
    Route::delete('playlist/{id}', 'PlayListController@playlistDelete');


    //profiles
    Route::get('profile/{id}', 'ProfileController@profileShow');
    Route::get('profileChangeStatus/{id}', 'ProfileController@ProfileChangeStatus');
    Route::post('profileCreate', 'ProfileController@profileCreate');
    Route::get('profileEdit/{id}', 'ProfileController@profileEdit');
    Route::patch('profileUpdate', 'ProfileController@profileUpdate');
    Route::patch('profilePasswordReset', 'ProfileController@profilePasswordReset');
    Route::delete('profile/{id}', 'ProfileController@profileDelete');
});
