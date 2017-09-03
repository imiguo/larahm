<?php

return [
    'payments' => [
        1  => ['name' => 'PerfectMoney', 'sfx' => 'perfectmoney', 'status' => 1],
        2 => ['name' => 'Payeer', 'sfx' => 'payeer', 'status' => 1],
        3 => ['name' => 'BitCoin', 'sfx' => 'bitcoin', 'status' => 1],
    ],

    'auto_blade' => env('AUTO_BLADE', true),
    'blade_path' => storage_path('blades'),
    'show_history' => env('SHOW_HISTORY', true),

    'transtype' => [
        'withdraw_pending'             => 'Withdrawal request',
        'add_funds'                    => 'Transfer from external processings',
        'deposit'                      => 'Deposit',
        'bonus'                        => 'Bonus',
        'penality'                     => 'Penalty',
        'earning'                      => 'Earning',
        'withdrawal'                   => 'Withdrawal',
        'commissions'                  => 'Referral commission',
        'early_deposit_release'        => 'Deposit release',
        'early_deposit_charge'         => 'Commission for an early deposit release',
        'release_deposit'              => 'Deposit returned to user account',
        'exchange_out'                 => ' Received on exchange',
        'exchange_in'                  => 'Spent on exchange',
        'exchange'                     => 'Exchange',
        'internal_transaction_spend'   => 'Spent on Internal Transaction',
        'internal_transaction_receive' => 'Received from Internal Transaction',
    ],

    'auto_fake' => env('AUTO_FAKE', false),
];
