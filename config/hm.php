<?php

return [
    'payments' => [
        1  => ['name' => 'PerfectMoney', 'sfx' => 'perfectmoney'],
        2 => ['name' => 'BitCoin', 'sfx' => 'bitcoin'],
        3 => ['name' => 'Payeer', 'sfx' => 'payeer'],
        4  => ['name' => 'e-gold', 'sfx' => 'egold'],
        5  => ['name' => 'INTGold', 'sfx' => 'intgold'],
        6  => ['name' => 'StormPay', 'sfx' => 'stormpay'],
        7  => ['name' => 'e-Bullion', 'sfx' => 'ebullion'],
        8  => ['name' => 'PayPal', 'sfx' => 'paypal'],
        9  => ['name' => 'GoldMoney', 'sfx' => 'goldmoney'],
        10  => ['name' => 'eeeCurrency', 'sfx' => 'eeecurrency'],
        11  => ['name' => 'Pecunix', 'sfx' => 'pecunix'],
    ],

    'auto_blade' => env('AUTO_BLADE', true),
    'blade_path' => storage_path('blades'),
    'show_history' => env('SHOW_HISTORY', true),
];
