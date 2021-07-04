<?php

Route::group(['middleware' => ['web']], function () {
    Route::get('/test/email/{templateId}/', function ($templateId) {
        $mc = Waka\Mailer\Classes\MailCreator::find($templateId);
        return '<div style="width:600px">' . $mc->renderTest() . '</div>';
    });
});
