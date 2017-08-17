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

include app_path('Hm').'/lib/config.inc.php';

$mymd5 = app('data')->settings['md5altphrase_eeecurrency'];
if (($mymd5 == app('data')->frm['HASH'] and (app('data')->frm['TRANSACTION_ID'] != '' and app('data')->exchange_systems[8]['status'] == 1))) {
    if (app('data')->frm['RESULT'] != '0') {
        throw new EmptyException();
    }

    $user_id = sprintf('%d', app('data')->frm['ITEM_NUMBER']);
    $h_id = sprintf('%d', app('data')->frm['CUSTOM2']);
    $compound = sprintf('%d', app('data')->frm['CUSTOM4']);
    $amount = app('data')->frm['AMOUNT'];
    $batch = app('data')->frm['TRANSACTION_ID'];
    $account = app('data')->frm['BUYERACCOUNTID'];
    if (app('data')->frm['CUSTOM3'] == 'checkpayment') {
        add_deposit(8, $user_id, $amount, $batch, $account, $h_id, $compound);
    }
}

echo '1';
