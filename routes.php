<?php

Route::group(['middleware' => ['web']], function () {
    Route::get('/test/email/{templateId}/', function ($templateId) {
        $mc = Waka\Mailer\Classes\MailCreator::find($templateId);
        return '<div style="width:600px">' . $mc->renderTest() . '</div>';
    });
});
Route::group(['middleware' => ['mailgunWebHook']], function () {
    Route::get('/api/mailgun/wo/{type}', function ($type) {
        $mc = Waka\Mailer\Classes\MailgunWo::create($type);
        return true;
    });
});
