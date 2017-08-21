<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

header('HTTP/1.1 202 Accepted');

app('data')->frm['transaction_id'] = sprintf('%d', app('data')->frm['transaction_id']); if (((((app('data')->frm['status'] == 'SUCCESS' and app('data')->exchange_systems[4]['status'] == 1) and app('data')->frm['secret_code'] == app('data')->settings['md5altphrase_stormpay']) and 0 < app('data')->frm['transaction_id']) and app('data')->frm['transaction_type'] == 'Payment')) {
    $user_id = sprintf('%d', app('data')->frm['user1']);
    $h_id = sprintf('%d', app('data')->frm['user2']);
    $compound = sprintf('%d', app('data')->frm['user4']);
    $amount = app('data')->frm['amount'];
    $batch = app('data')->frm['transaction_id'];
    $account = app('data')->frm['payer_email'];
    if (app('data')->frm['user3'] == 'checkpayment') {
        add_deposit(4, $user_id, $amount, $batch, $account, $h_id, $compound);
    }
}

echo '1';
