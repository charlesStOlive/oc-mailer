<?php
return [
    'btns' => [
        'mail' => [
            'label' => 'waka.mailer::wakamail.create',
            'class' => 'btn-secondary',
            'ajaxCaller' => 'onLoadMailBehaviorPopupForm',
            'ajaxInlineCaller' => 'onLoadMailBehaviorContentForm',
            'icon' => 'oc-icon-envelope',
        ],
        'lot_mail' => [
            'label' => 'waka.mailer::wakamail.lot_email',
            'class' => 'btn-secondary',
            'ajaxInlineCaller' => 'onLotMail',
            'icon' => 'oc-icon-envelope',
        ],
    ],
    'mailgun_webhooks' =>  [
        'signing_key' => env('MAILGUN_SECRET'),
    ]
];
