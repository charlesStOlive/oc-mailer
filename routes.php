<?php

Route::group(['middleware' => ['web']], function () {
    Route::get('/test/email/{templateId}', function ($templateId) {
        $wc = new Waka\Mailer\Classes\MailCreator($templateId);
        return '<div style="width:600px;text-align:center">' . $wc->renderMail(null, null, true) . '</div>';
    });
});
