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

$request = app('request');

$payment_id = $request->input('PAYMENT_ID');
$gate = $payment_id[0] == 1 ? 'low' : 'high';

$hash_alternate_passphrase = strtoupper(md5(psconfig('pm.alternate_passphrase', $gate)));
$string = $payment_id.':'.$request->input('PAYEE_ACCOUNT').':'.
          $request->input('PAYMENT_AMOUNT').':'.$request->input('PAYMENT_UNITS').':'.
          $request->input('PAYMENT_BATCH_NUM').':'.
          $request->input('PAYER_ACCOUNT').':'.$hash_alternate_passphrase.':'.
          $request->input('TIMESTAMPGMT');
$hash = strtoupper(md5($string));

if ($hash == $request->input('V2_HASH')) {
    $payment_id = sprintf('%d', $payment_id);

    $order = Order::where('order_no', $payment_id)->first();
    $h_id = $order->data['plan_id'];
    $compound = $order->data['compound'];
    $user_id = $order->user_id;

    $amount = $request->input('PAYMENT_AMOUNT');
    $batch = $request->input('PAYMENT_BATCH_NUM');
    $account = $request->input('PAYER_ACCOUNT');
    if ($request->input('PAYMENT_UNITS') == 'USD') {
        add_deposit(1, $user_id, $amount, $batch, $account, $h_id, $compound);
        $order->status = Order::STATUS_OK;
        $order->save();
    }
}

echo '1';
