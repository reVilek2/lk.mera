<?php

return [
    'default_driver' => env('DEFAULT_SMS_SERVICE', 'sms_ru'),
    'sms_ru'  => [
        'api_id' => env('SMSRU_API_ID'),
        'is_test' => env('SMSRU_TEST', true),
    ],
];