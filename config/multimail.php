<?php

return [
    /*
    |--------------------------------------------------------------------------
    | List your email providers
    |--------------------------------------------------------------------------
    |
    | Enjoy a life with multimail
    |
    */

    'emails'  => [
        'recommendation_accepted' =>
            [
              'pass'          => env('MULTIMAIL_RECOMMENDATION_ACCEPTED_PASSWORD'),
              'username'      => env('MULTIMAIL_RECOMMENDATION_ACCEPTED_USERNAME'),
              'from_mail'     => env('MULTIMAIL_RECOMMENDATION_ACCEPTED_FROM_ADDRESS'),
              'from_name'     => env('MULTIMAIL_RECOMMENDATION_ACCEPTED_FROM_NAME'),
            ],
        'client_confirmation' =>
            [
              'pass'          => env('MULTIMAIL_CLIENT_CONFIRMATIOM_PASSWORD'),
              'username'      => env('MULTIMAIL_CLIENT_CONFIRMATIOM_USERNAME'),
              'from_mail'     => env('MULTIMAIL_CLIENT_CONFIRMATIOM_FROM_ADDRESS'),
              'from_name'     => env('MULTIMAIL_CLIENT_CONFIRMATIOM_FROM_NAME'),
            ]
    ],

    'provider' => [
      'default' =>
        [
          'host'      => env('MULTIMAIL_GOOGLE_HOST'),
          'port'      => env('MULTIMAIL_GOOGLE_PORT'),
          'encryption' => env('MULTIMAIL_GOOGLE_ENCRYPTION'),
        ]
    ],
];
