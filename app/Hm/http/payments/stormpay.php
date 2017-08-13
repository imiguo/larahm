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
include app_path('Hm').'/lib/config.inc.php';

$frm['transaction_id'] = sprintf('%d', $frm['transaction_id']); if ((((($frm['status'] == 'SUCCESS' and $exchange_systems[4]['status'] == 1) and $frm['secret_code'] == $settings['md5altphrase_stormpay']) and 0 < $frm['transaction_id']) and $frm['transaction_type'] == 'Payment')) {
    $user_id = sprintf('%d', $frm['user1']);
    $h_id = sprintf('%d', $frm['user2']);
    $compound = sprintf('%d', $frm['user4']);
    $amount = $frm['amount'];
    $batch = $frm['transaction_id'];
    $account = $frm['payer_email'];
    if ($frm['user3'] == 'checkpayment') {
        add_deposit(4, $user_id, $amount, $batch, $account, $h_id, $compound);
    }
}

echo '1';
