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
            ],
        'recovery_password' => 
            [
              'pass'          => env('MULTIMAIL_RECOVERY_PASSWORD'),
              'username'      => env('MULTIMAIL_RECOVERY_USERNAME'),
              'from_mail'     => env('MULTIMAIL_RECOVERY_FROM_ADDRESS'),
              'from_name'     => env('MULTIMAIL_RECOVERY_FROM_NAME'),
              'provider'      => 
                [
                  'host'      => env('MAIL_HOST'),
                  'port'      => env('MAIL_PORT'),
                  'encryption' => env('MAIL_ENCRYPTION'),
                ],
            ]
    ],

    'provider' => [
      'default' =>
        [
          'host'      => env('MULTIMAIL_GOOGLE_HOST'),
          'port'      => env('MULTIMAIL_GOOGLE_PORT'),
          'encryption' => env('MULTIMAIL_GOOGLE_ENCRYPTION'),
        ],
    ],
];
