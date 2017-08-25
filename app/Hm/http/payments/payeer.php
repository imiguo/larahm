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

file_put_contents('../log/payeer_processing_'.env('APP_ENV').'.txt', json_encode(app('data')->frm).PHP_EOL, FILE_APPEND);
file_put_contents('../log/payeer_processing_'.env('APP_ENV').'.txt', 'IP:'.app('data')->env['REMOTE_ADDR'].PHP_EOL, FILE_APPEND);

// Rejecting queries from IP addresses not belonging to Payeer
if (! in_array($_SERVER['REMOTE_ADDR'], ['185.71.65.92', '185.71.65.189', '149.202.17.210', ])) {
    throw new EmptyException();
}
if (isset($_POST['m_operation_id']) && isset($_POST['m_sign'])) {
    $orderid = $_POST['m_orderid'];
    $gate = $payment_id[0] == 1 ? 'low' : 'hight';
    $m_secret_key = psconfig('payeer.shop_secret_key', $gate);
    // Forming an array for signature generation
    $arHash = [
        $_POST['m_operation_id'],
        $_POST['m_operation_ps'],
        $_POST['m_operation_date'],
        $_POST['m_operation_pay_date'],
        $_POST['m_shop'],
        $orderid,
        $_POST['m_amount'],
        $_POST['m_curr'],
        $_POST['m_desc'],
        $_POST['m_status'],
    ];
    // Adding additional parameters to the array if such parameters have been transferred
    if (isset($_POST['m_params'])) {
        $arHash[] = $_POST['m_params'];
    }
    // Adding the secret key to the array
    $arHash[] = $m_secret_key;
    // Forming a signature
    $sign_hash = strtoupper(hash('sha256', implode(':', $arHash)));
    // If the signatures match and payment status is “Complete”
    if ($_POST['m_sign'] == $sign_hash && $_POST['m_status'] == 'success') {
        $orderNo = $orderid;
        $order = Order::where('order_no', $payment_id)->first();
        $h_id = $order->data['plan_id'];
        $compound = $order->data['compound'];
        $user_id = $order->user_id;

        add_deposit(2, $user_id, $_POST['m_amount'], $_POST['m_operation_id'], $_POST['client_account'], $h_id, $compound);
        $order->status = Order::STATUS_OK;
        $order->save();

        // Here you can mark the invoice as paid or transfer funds to your customer
        // Returning that the payment was processed successfully
        echo $orderid.'|success';
        throw new EmptyException();
    }
    // If not, returning an error
    echo $orderid.'|error';
    throw new EmptyException();
}

echo '1';
