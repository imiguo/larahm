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

Log::info('asmoney_processing', [
    'env' => env('APP_ENV'),
    'frm' => app('data')->frm,
    'ip' => app('data')->env['REMOTE_ADDR'],
]);

$request = app('request');

$payment_id = $request->input('PAYMENT_ID');
$gate = $payment_id[0] == 1 ? 'low' : 'high';

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
