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

file_put_contents('../log/asmoney_processing_'.env('APP_ENV').'.txt', json_encode(app('data')->frm).PHP_EOL, FILE_APPEND);
file_put_contents('../log/asmoney_processing_'.env('APP_ENV').'.txt', 'IP:'.app('data')->env['REMOTE_ADDR'].PHP_EOL, FILE_APPEND);

$request = app('request');

$payment_id = $request->input('PAYMENT_ID');
$gate = $payment_id[0] == 1 ? 'low' : 'hight';

$params = [
    $request->input('PAYEE_ACCOUNT'),
    $request->input('PAYER_ACCOUNT'),
    $request->input('PAYMENT_AMOUNT'),
    $request->input('PAYMENT_UNITS'),
    $request->input('BATCH_NUM'),
    $payment_id,
    $request->input('PAYMENT_STATUS'),
    md5(psconfig('asmoney.store_password', $gate)),
];
if ($request->input('MD5_HASH') == implode('|', $params)) {
    $order = Order::where('order_no', $payment_id)->first();
    $h_id = $order->data['plan_id'];
    $compound = $order->data['compound'];
    $user_id = $order->user_id;

    $amount = app('data')->frm['PAYMENT_AMOUNT'];
    $batch = app('data')->frm['BATCH_NUM'];
    $account = app('data')->frm['PAYER_ACCOUNT'];
    if (app('data')->frm['PAYMENT_UNITS'] == 'USD') {
        add_deposit(3, $user_id, $amount, $batch, $account, $h_id, $compound);
        $order->status = Order::STATUS_OK;
        $order->save();
    }
}

echo '1';
