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

echo ' <b>Process Withdrawal:</b><br><br>';

$id = sprintf('%d', app('data')->frm['id']);
$q = 'select * from history where id='.$id.' and type=\'withdraw_pending\'';
$sth = db_query($q);
$do_not_show_form = 0;
if ($trans = mysql_fetch_array($sth)) {
    $q = 'select * from users where id = '.$trans['user_id'];
    $sth1 = db_query($q);
    if ($user = mysql_fetch_array($sth1)) {
    } else {
        echo 'User not found!';
        throw new EmptyException();
    }

    if ($trans['str'] == '') {
        $str = gen_confirm_code(30);
        $q = 'update history set str = \''.$str.'\' where id ='.$id;
        db_query($q);
        $trans['str'] = $str;
    }
} else {
    if (app('data')->frm['say'] == 'yes') {
        echo 'Transaction successfully processed';
    } else {
        if (app('data')->frm['say'] == 'no') {
            echo 'Transaction was not processed';
        } else {
            echo 'Request not found!';
        }
    }

    $do_not_show_form = 1;
}

$amount = abs($trans['actual_amount']);
$fee = floor($amount * app('data')->settings['withdrawal_fee']) / 100;
if ($fee < app('data')->settings['withdrawal_fee_min']) {
    $fee = app('data')->settings['withdrawal_fee_min'];
}

$to_withdraw = $amount - $fee;
if ($to_withdraw < 0) {
    $to_withdraw = 0;
}

$to_withdraw = sprintf('%.02f', floor($to_withdraw * 100) / 100);
