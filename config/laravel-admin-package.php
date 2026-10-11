<?php

return [
    'admin_url' => 'admin',
    // Legacy environment variable: true has always meant package views first.
    'package_views_first' => env('MAXI032_LAP_LOAD_VIEWS_FROM_VENDOR_FIRST', false),
    'allowed_languages' => [
        [
            'id' => 1,
            'name' => 'English',
            'code' => 'en',
        ],
        [
            'id' => 2,
            'name' => 'Nederlands',
            'code' => 'nl',
        ],
    ],
];
