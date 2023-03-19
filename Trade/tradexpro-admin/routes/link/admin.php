<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'namespace' => 'admin', 'middleware' => ['auth', 'admin', 'permission', 'default_lang']], function () {

    // Logs
    Route::group(['group' => 'log'], function () {
        Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->name('adminLogs')->per('log');
    });

    Route::group(['group' => 'dashboard'], function () {
        Route::get('dashboard', 'DashboardController@adminDashboard')->name('adminDashboard');
        Route::get('pending-withdrawals', 'TransactionController@adminPendingWithdrawal')->name('adminPendingWithdrawals');
    });
    Route::get('earning-report', 'DashboardController@adminEarningReport')->name('adminEarningReport');

    // user management
    require base_path('routes/link/userManagement.php');

    // coin management
    require base_path('routes/link/coinManagement.php');

    // wallet deposit withdrawal management
    require base_path('routes/link/walletManagement.php');

    // general settings
    require base_path('routes/link/generalSettings.php');

    // landing settings
    require base_path('routes/link/landingManagement.php');

    // fiat management
    require base_path('routes/link/fiatManagement.php');

    // trade management
    require base_path('routes/link/tradeManagement.php');

    // trade management
    require base_path('routes/link/roleManagement.php');

   // notification
    Route::group(['group' => 'notify'], function () {
        Route::get('send-notification', 'DashboardController@sendNotification')->name('sendNotification');
        Route::post('send-notification-process', 'DashboardController@sendNotificationProcess')->name('sendNotificationProcess');
    });
    Route::group(['group' => 'email'], function () {
        Route::get('send-email', 'DashboardController@sendEmail')->name('sendEmail');
        Route::get('clear-email', 'DashboardController@clearEmailRecord')->name('clearEmailRecord');
        Route::post('send-email-process', 'DashboardController@sendEmailProcess')->name('sendEmailProcess')->middleware('check_demo');
    });

});

Route::group(['middleware'=> ['auth', 'lang']], function () {
    Route::get('/send-sms-for-verification', 'user\ProfileController@sendSMS')->name('sendSMS');
    Route::get('test', 'TestController@index')->name('test');
    Route::group(['middleware'=>'check_demo'], function() {
        Route::post('/user-profile-update', 'user\ProfileController@userProfileUpdate')->name('userProfileUpdate');
        Route::post('/upload-profile-image', 'user\ProfileController@uploadProfileImage')->name('uploadProfileImage');
        Route::post('change-password-save', 'user\ProfileController@changePasswordSave')->name('changePasswordSave');
        Route::post('/phone-verify', 'user\ProfileController@phoneVerify')->name('phoneVerify');
    });
});
