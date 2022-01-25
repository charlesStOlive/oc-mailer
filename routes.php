<?php

Route::group(['middleware' => ['web']], function () {
    Route::get('/test/email/{templateId}/', function ($templateId) {
            return '<div transform: scale(1);">' . Waka\Mailer\Classes\MailCreator::find($templateId)->renderTest() . '</div>';
    });
});
Route::group(['middleware' => ['Waka\Mailer\Classes\Middleware\MailgunWebHook']], function () {
    Route::prefix('/api/mailgun/wo')->group(function () {
        Route::post('{type}',  '\Waka\Mailer\Classes\MailgunWebHook@messageType')->name('messageType');
    });
});
