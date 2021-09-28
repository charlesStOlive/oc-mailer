<?php

Route::group(['middleware' => ['web']], function () {
    Route::get('/test/email/{templateId}/', function ($templateId) {
        $mc = Waka\Mailer\Classes\MailCreator::find($templateId);
        return '<div style="width:600px">' . $mc->renderTest() . '</div>';
    });
});
Route::group(['middleware' => ['Waka\Mailer\Classes\Middleware\MailgunWebHook']], function () {
    Route::prefix('/api/mailgun/wo')->group(function () {
        Route::post('{type}',  '\Waka\Mailer\Classes\MailgunWebHook@messageType')->name('messageType');
    });
});
