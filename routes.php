<?php

Route::group(['middleware' => ['web']], function () {
    Route::get('/test/email/{templateId}/', function ($templateId) {
        $wc = Waka\Mailer\Classes\MailCreator::find($templateId);
        $mail = new Waka\Mailer\Models\WakaMail();
        $mail = $mail->find($templateId);

        return '<div style="width:600px;text-align:center">' . $wc->renderTest($mail->test_id, null, true) . '</div>';
    });
});
