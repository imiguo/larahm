<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Models\Order;

Log::info('perfectmoney_processing', [
    'env' => env('APP_ENV'),
    'frm' => app('data')->frm,
    'ip' => app('data')->env['REMOTE_ADDR'],
]);

$payment_id = app('data')->frm['PAYMENT_ID'];
$gate = $payment_id[0] == 1 ? 'low' : 'hight';

$string = $payment_id.':'.app('data')->frm['PAYEE_ACCOUNT'].':'.
          app('data')->frm['PAYMENT_AMOUNT'].':'.app('data')->frm['PAYMENT_UNITS'].':'.
          app('data')->frm['PAYMENT_BATCH_NUM'].':'.
          app('data')->frm['PAYER_ACCOUNT'].':'.md5(psconfig('pm.alternate_passphrase'), $gate).':'.
          app('data')->frm['TIMESTAMPGMT'];
$hash = strtoupper(md5($string));

if ($hash == app('data')->frm['V2_HASH']) {
    // $ip = app('data')->env['REMOTE_ADDR'];
    // if ( ! preg_match('/63\\.240\\.230\\.\\d/i', $ip)) {
    //     throw new EmptyException();
    // }

    $payment_id = sprintf('%d', $payment_id);

    $order = Order::where('order_no', $payment_id)->first();
    $h_id = $order->data['plan_id'];
    $compound = $order->data['compound'];
    $user_id = $order->user_id;

    $amount = app('data')->frm['PAYMENT_AMOUNT'];
    $batch = app('data')->frm['PAYMENT_BATCH_NUM'];
    $account = app('data')->frm['PAYER_ACCOUNT'];
    if (app('data')->frm['PAYMENT_UNITS'] == 'USD') {
        add_deposit(1, $user_id, $amount, $batch, $account, $h_id, $compound);
        $order->status = Order::STATUS_OK;
        $order->save();
    }
}

echo '1';
