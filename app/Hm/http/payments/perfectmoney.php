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

file_put_contents('../log/perfectmoney_processing_'.env('APP_ENV').'.txt', json_encode(app('data')->frm).PHP_EOL, FILE_APPEND);
file_put_contents('../log/perfectmoney_processing_'.env('APP_ENV').'.txt', 'IP:'.app('data')->env['REMOTE_ADDR'].PHP_EOL, FILE_APPEND);

$mymd5 = app('data')->settings['md5altphrase_perfectmoney'];

$string = app('data')->frm['PAYMENT_ID'].':'.app('data')->frm['PAYEE_ACCOUNT'].':'.
          app('data')->frm['PAYMENT_AMOUNT'].':'.app('data')->frm['PAYMENT_UNITS'].':'.
          app('data')->frm['PAYMENT_BATCH_NUM'].':'.
          app('data')->frm['PAYER_ACCOUNT'].':'.$mymd5.':'.
          app('data')->frm['TIMESTAMPGMT'];
$hash = strtoupper(md5($string));

if ($hash == app('data')->frm['V2_HASH']) {
    // $ip = app('data')->env['REMOTE_ADDR'];
    // if ( ! preg_match('/63\\.240\\.230\\.\\d/i', $ip)) {
    //     throw new EmptyException();
    // }

    $payment_id = sprintf('%d', app('data')->frm['PAYMENT_ID']);

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
