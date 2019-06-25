<?php

return [
    'default_driver' => env('DEFAULT_PAYMENT_SYSTEM', 'yandex'),
    'yandex'  => [
        'merchantId' => env('YANDEX_SHOP_ID'),
        'scid'       => env('YANDEX_SCID'),
        'secretKey'  => env('YANDEX_SECRET_KEY'),
        'successURL' => env('YANDEX_SUCCESS_URL', '/'),
        'failURL'    => env('YANDEX_FAIL_URL', '/'),
        'isTest'     => env('PAYMENT_SYSTEM_TEST', true),
    ],
];