<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Exceptions\EmptyException;
use App\Models\Order;

$request = app('request');

// Rejecting queries from IP addresses not belonging to Payeer
if (! in_array($request->getClientIp(), ['185.71.65.92', '185.71.65.189', '149.202.17.210', ])) {
    throw new EmptyException();
}
if ($request->input('m_operation_id') && $request->input('m_sign')) {
    $orderid = $request->input('m_orderid');
    $gate = $payment_id[0] == 1 ? 'low' : 'high';
    $m_secret_key = psconfig('payeer.shop_secret_key', $gate);
    // Forming an array for signature generation
    $arHash = [
        $request->input('m_operation_id'),
        $request->input('m_operation_ps'),
        $request->input('m_operation_date'),
        $request->input('m_operation_pay_date'),
        $request->input('m_shop'),
        $orderid,
        $request->input('m_amount'),
        $request->input('m_curr'),
        $request->input('m_desc'),
        $request->input('m_status'),
    ];
    // Adding additional parameters to the array if such parameters have been transferred
    if (isset($request->input('m_params'))) {
        $arHash[] = $request->input('m_params');
    }
    // Adding the secret key to the array
    $arHash[] = $m_secret_key;
    // Forming a signature
    $sign_hash = strtoupper(hash('sha256', implode(':', $arHash)));
    // If the signatures match and payment status is “Complete”
    if ($request->input('m_sign') == $sign_hash && $request->input('m_status') == 'success') {
        $orderNo = $orderid;
        $order = Order::where('order_no', $payment_id)->first();
        $h_id = $order->data['plan_id'];
        $compound = $order->data['compound'];
        $user_id = $order->user_id;

        add_deposit(2, $user_id, $request->input('m_amount'), $request->input('m_operation_id'), $request->input('client_account'), $h_id, $compound);
        $order->status = Order::STATUS_OK;
        $order->save();

        // Here you can mark the invoice as paid or transfer funds to your customer
        // Returning that the payment was processed successfully
        echo $orderid.'|success';
        throw new EmptyException();
    }
    // If not, returning an error
    echo $orderid.'|error';
}

echo '1';
