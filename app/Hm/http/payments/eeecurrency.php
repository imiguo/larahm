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

$mymd5 = $settings['md5altphrase_eeecurrency'];
if (($mymd5 == $frm['HASH'] and ($frm['TRANSACTION_ID'] != '' and $exchange_systems[8]['status'] == 1))) {
    if ($frm['RESULT'] != '0') {
        throw new EmptyException();
    }

    $user_id = sprintf('%d', $frm['ITEM_NUMBER']);
    $h_id = sprintf('%d', $frm['CUSTOM2']);
    $compound = sprintf('%d', $frm['CUSTOM4']);
    $amount = $frm['AMOUNT'];
    $batch = $frm['TRANSACTION_ID'];
    $account = $frm['BUYERACCOUNTID'];
    if ($frm['CUSTOM3'] == 'checkpayment') {
        add_deposit(8, $user_id, $amount, $batch, $account, $h_id, $compound);
    }
}

echo '1';
