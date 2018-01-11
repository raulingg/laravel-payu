<?php

return [
    'merchantId' => env('PAYU_MERCHANT_ID'),
    'apiLogin' => env('PAYU_API_LOGIN'),
    'apiKey' => env('PAYU_API_KEY'),
    'accountId' => env('PAYU_ACCOUNT_ID'),
    'country' => env('PAYU_COUNTRY', 'PE'),
    'isTest' => env('PAYU_ON_TESTING', false),
];
