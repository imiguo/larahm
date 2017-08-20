<?php

return [
    'theme' => theme(),

    'payments' => [
        0  => ['name' => 'e-gold', 'sfx' => 'egold'],
        2  => ['name' => 'INTGold', 'sfx' => 'intgold'],
        3  => ['name' => 'PerfectMoney', 'sfx' => 'perfectmoney'],
        4  => ['name' => 'StormPay', 'sfx' => 'stormpay'],
        5  => ['name' => 'e-Bullion', 'sfx' => 'ebullion'],
        6  => ['name' => 'PayPal', 'sfx' => 'paypal'],
        7  => ['name' => 'GoldMoney', 'sfx' => 'goldmoney'],
        8  => ['name' => 'eeeCurrency', 'sfx' => 'eeecurrency'],
        9  => ['name' => 'Pecunix', 'sfx' => 'pecunix'],
        10 => ['name' => 'Payeer', 'sfx' => 'payeer'],
        11 => ['name' => 'BitCoin', 'sfx' => 'bitcoin'],
    ],

    'auto_blade' => env('AUTO_BLADE', true),
    'blade_path' => storage_path('blades'),
    'show_history' => env('SHOW_HISTORY', true),
];
