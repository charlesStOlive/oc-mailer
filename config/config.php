<?php
return [
    'btns' => [
        'mail' => [
            'label' => 'Email',
            'class' => 'btn-secondary',
            'ajaxCaller' => 'onLoadMailBehaviorPopupForm',
            'ajaxInlineCaller' => 'onLoadMailBehaviorContentForm',
            'icon' => 'oc-icon-envelope',
        ],
        'lot_mail' => [
            'label' => 'Lot Emails',
            'class' => 'btn-secondary',
            'ajaxInlineCaller' => 'onLotMail',
            'icon' => 'oc-icon-envelope',
        ],
    ],
];
