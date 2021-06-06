<?php

return [
    'default_driver' => env('DEFAULT_PAYMENT_SYSTEM', 'yandex'),
    'yandex'  => [
        'merchantId' => env('YANDEX_SHOP_ID'),
        'scid'       => env('YANDEX_SCID'),
        'secretKey'  => env('YANDEX_SECRET_KEY'),
    ],
    'tinkoff'  => [
        'merchantId'    => env('TINKOFF_TERMINAL_KEY'),
        'secretKey'       => env('TINKOFF_TERMINAL_PASSWORD'),
    ],
    'paykeeper' => [
        'server' => env('PAYKEEPER_SERVER'),
        'user'   => env('PAYKEEPER_USER'),
        'pass'   => env('PAYKEEPER_PASS'),
        'secret' => env('PAYKEEPER_SECRET'),
    ],
];
