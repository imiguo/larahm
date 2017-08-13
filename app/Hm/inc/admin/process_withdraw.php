<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

echo ' <b>Process Withdrawal:</b><br><br>';

$id = sprintf('%d', $frm['id']);
$q = 'select * from hm2_history where id='.$id.' and type=\'withdraw_pending\'';
$sth = db_query($q);
$do_not_show_form = 0;
if ($trans = mysql_fetch_array($sth)) {
    $q = 'select * from hm2_users where id = '.$trans['user_id'];
    $sth1 = db_query($q);
    if ($user = mysql_fetch_array($sth1)) {
    } else {
        echo 'User not found!';
        exit();
    }

    if ($trans['str'] == '') {
        $str = gen_confirm_code(30);
        $q = 'update hm2_history set str = \''.$str.'\' where id ='.$id;
        db_query($q);
        $trans['str'] = $str;
    }
} else {
    if ($frm['say'] == 'yes') {
        echo 'Transaction successfully processed';
    } else {
        if ($frm['say'] == 'no') {
            echo 'Transaction was not processed';
        } else {
            echo 'Request not found!';
        }
    }

    $do_not_show_form = 1;
}

$amount = abs($trans['actual_amount']);
$fee = floor($amount * $settings['withdrawal_fee']) / 100;
if ($fee < $settings['withdrawal_fee_min']) {
    $fee = $settings['withdrawal_fee_min'];
}

$to_withdraw = $amount - $fee;
if ($to_withdraw < 0) {
    $to_withdraw = 0;
}

$to_withdraw = sprintf('%.02f', floor($to_withdraw * 100) / 100);

if ($do_not_show_form == 0) {
    if ($trans['ec'] == 0) {
        echo '<form name=spend method=post action="https://www.e-gold.com/sci_asp/payments.asp">
<input type=hidden name=withdraw value="';
        echo $id;
        echo '-';
        echo $trans['str'];
        echo '">
<input type=hidden name=a value="pay_withdraw">
<INPUT type=hidden name=PAYMENT_AMOUNT value="';
        echo $to_withdraw;
        echo '">
<INPUT type=hidden name=PAYEE_ACCOUNT value="';
        echo $user['egold_account'];
        echo '">
<INPUT type=hidden name=PAYEE_NAME value="';
        echo $user['name'];
        echo '" >
Sending <b>$';
        echo $to_withdraw;
        echo '</b> to e-gold.<br>
Payment will be made from this account:<br><br> <INPUT type=text class=inpts name=FORCED_PAYER_ACCOUNT value="';
        echo $settings['def_payee_account'];
        echo '">
<INPUT type=hidden name=PAYMENT_UNITS value="1">
<INPUT type=hidden name=PAYMENT_METAL_ID value="1">
<INPUT type=hidden name=STATUS_URL value="';
        echo $settings['site_url'];
        echo '/egold_processing.php">
<INPUT type=hidden name=PAYMENT_URL value="';
        echo $frm_env['HTTPS'] ? 'https' : 'http';
        echo '://';
        echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
        echo '?a=pay_withdraw&say=yes">
<INPUT type=hidden name=NOPAYMENT_URL value="';
        echo $frm_env['HTTPS'] ? 'https' : 'http';
        echo '://';
        echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
        echo '?a=pay_withdraw&say=no">
<INPUT type=hidden name=BAGGAGE_FIELDS value="withdraw a">
<INPUT type=hidden value="Withdraw to ';
        echo $user['name'];
        echo ' from ';
        echo $settings['site_name'];
        echo '" name=SUGGESTED_MEMO>
<br>
<br><input type=submit value="Go to e-gold.com" class=sbmt> &nbsp;
<input type=button class=sbmt value="Cancel" onclick="window.close();">
</form>';
    } else {
        if ($trans['ec'] == 1) {
            echo 'Sending <b>$';
            echo $to_withdraw;
            echo '</b> to EvoCash.<br>
<form name=spend method=post action="https://www.evocash.com/evoswift/index.cfm">
<input type=hidden name=withdraw value="';
            echo $id;
            echo '-';
            echo $trans['str'];
            echo '">
<input type=hidden name=a value="pay_withdraw">
<INPUT type=hidden name=amount value="';
            echo $to_withdraw;
            echo '">
<INPUT type=hidden name=receivingaccountid value="';
            echo $user['evocash_account'];
            echo '">
<INPUT type=hidden name=merchant_check_url value="';
            echo $settings['site_url'];
            echo '/evocash_processing.php">
<INPUT type=hidden name=pay_yes_url value="';
            echo $frm_env['HTTPS'] ? 'https' : 'http';
            echo '://';
            echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
            echo '?a=pay_withdraw&say=yes">
<INPUT type=hidden name=pay_no_url value="';
            echo $frm_env['HTTPS'] ? 'https' : 'http';
            echo '://';
            echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
            echo '?a=pay_withdraw&say=no">
<INPUT type=hidden name=baggage_fields value="withdraw a">
<INPUT type=hidden name=pay_yes_url_method value=POST>
<INPUT type=hidden name=pay_no_url_method value=POST>
<INPUT type=hidden value="Withdraw to ';
            echo $user['name'];
            echo ' from ';
            echo $settings['site_name'];
            echo '" name=memo>
<br>
<br><input type=submit value="Go to evocash.com" class=sbmt> &nbsp;
<input type=button class=sbmt value="Cancel" onclick="window.close();">
</form>';
        } else {
            if ($trans['ec'] == 2) {
                echo 'Sending <b>$';
                echo $to_withdraw;
                echo '</b> to INTGold.<br>
<form name=spend method=post action="https://intgold.com/cgi-bin/webshoppingcart.cgi">
<input type="hidden" name="cmd" value="_xclick">
<input type=hidden name=CUSTOM1 value="';
                echo $id;
                echo '-';
                echo $trans['str'];
                echo '">
<input type=hidden name=CUSTOM2 value="pay_withdraw">
<INPUT type=hidden name=AMOUNT value="';
                echo $to_withdraw;
                echo '">
<INPUT type=hidden name=SELLERACCOUNTID value="';
                echo $user['intgold_account'];
                echo '">
<INPUT type=hidden name=POSTURL value="';
                echo $settings['site_url'];
                echo '/">
<INPUT type=hidden name=RETURNURL value="';
                echo $frm_env['HTTPS'] ? 'https' : 'http';
                echo '://';
                echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                echo '?a=pay_withdraw&say=yes">
<INPUT type=hidden name=CANCEL_RETURN value="';
                echo $frm_env['HTTPS'] ? 'https' : 'http';
                echo '://';
                echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                echo '?a=pay_withdraw&say=no">
<input type="hidden" name="METHOD" value="POST">
<input type="hidden" name="RETURNPAGE" value="CGI">
<input type="hidden" name="ITEM_NUMBER" value="1">
<INPUT type=hidden value="Withdraw to ';
                echo $user['name'];
                echo ' from ';
                echo $settings['site_name'];
                echo '" name=ITEM_NAME>
<br>
<br><input type=submit value="Go to intgold.com" class=sbmt> &nbsp;
<input type=button class=sbmt value="Cancel" onclick="window.close();">
</form>';
            } else {
                if ($trans['ec'] == 4) {
                    echo 'Sending <b>$';
                    echo $to_withdraw;
                    echo '</b> to StormPay.<br>
<form method="post" action="https://www.stormpay.com/stormpay/handle_gen.php" target="_blank">
  <input type="hidden" name="product_name" value="Withdraw to ';
                    echo $user['name'];
                    echo ' from ';
                    echo $settings['site_name'];
                    echo '">
  <input type="hidden" name="amount" value="';
                    echo $to_withdraw;
                    echo '">
  <input type="hidden" name="payee_email" value="';
                    echo $user['stormpay_account'];
                    echo '">

  <input type="hidden" name="require_IPN" value="1">
  <input type="hidden" name="return_URL" value="';
                    echo $frm_env['HTTPS'] ? 'https' : 'http';
                    echo '://';
                    echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                    echo '?a=pay_withdraw&say=yes">
  <input type="hidden" name="notify_URL" value="';
                    echo $settings['site_url'];
                    echo '/">

  <input type="hidden" name="cancel_URL" value="';
                    echo $frm_env['HTTPS'] ? 'https' : 'http';
                    echo '://';
                    echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                    echo '?a=pay_withdraw&say=no">
  <input type=hidden name=user1 value="';
                    echo $id;
                    echo '-';
                    echo $trans['str'];
                    echo '">
  <input type=hidden name=user2 value=0>
  <input type=hidden name=user3 value=pay_withdraw>
<br>
<br><input type=submit value="Go to stormpay.com" class=sbmt> &nbsp;
<input type=button class=sbmt value="Cancel" onclick="window.close();">
</form>';
                } else {
                    if ($trans['ec'] == 5) {
                        echo 'Sending <b>$';
                        echo $to_withdraw;
                        echo '</b> to e-Bullion.<br>
<form name="spend" method="post" action="https://atip.e-bullion.com/process.php">
<input type=hidden name=withdraw value="';
                        echo $id;
                        echo '-';
                        echo $trans['str'];
                        echo '">
<input type=hidden name=a value="pay_withdraw">
<input type="hidden" name="ATIP_STATUS_URL" value="';
                        echo $settings['site_url'];
                        echo '/ebullion_processing.php">
<input type="hidden" name="ATIP_STATUS_URL_METHOD" value="POST">
<input type="hidden" name="ATIP_BAGGAGE_FIELDS" value="a withdraw">
<input type="hidden" name="ATIP_SUGGESTED_MEMO" value="Withdraw to ';
                        echo $user['name'];
                        echo ' from ';
                        echo $settings['site_name'];
                        echo '">
<input type="hidden" name="ATIP_PAYER_FEE_AMOUNT" value="0.00">
<input type="hidden" name="ATIP_PAYMENT_URL" value="';
                        echo $frm_env['HTTPS'] ? 'https' : 'http';
                        echo '://';
                        echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                        echo '?a=pay_withdraw&say=yes">
<input type="hidden" name="ATIP_PAYMENT_URL_METHOD" value="POST">
<input type="hidden" name="ATIP_NOPAYMENT_URL" value="';
                        echo $frm_env['HTTPS'] ? 'https' : 'http';
                        echo '://';
                        echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                        echo '?a=pay_withdraw&say=no">
<input type="hidden" name="ATIP_NOPAYMENT_URL_METHOD" value="POST">
<input type="hidden" name="ATIP_PAYMENT_FIXED" value="0">
<input type="hidden" name="ATIP_PAYMENT_AMOUNT" value="';
                        echo $to_withdraw;
                        echo '">
<input type="hidden" name="ATIP_PAYMENT_UNIT" value="1">
<input type="hidden" name="ATIP_PAYMENT_METAL" value="1">
<input type="hidden" name="ATIP_PAYEE_ACCOUNT" value="';
                        echo $user['ebullion_account'];
                        echo '">
<input type="hidden" name="ATIP_PAYEE_NAME" value="';
                        echo $user['name'];
                        echo '">
<br><input type=submit value="Go to e-Bullion.com" class=sbmt> &nbsp;
<input type=button class=sbmt value="Cancel" onclick="window.close();">
</form>';
                    } else {
                        if ($trans['ec'] == 6) {
                            echo 'Sending <b>$';
                            echo $to_withdraw;
                            echo '</b> to PayPal.<br>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
  <input type="hidden" name="cmd" value="_xclick">
  <input type="hidden" name="business" value="';
                            echo $user['paypal_account'];
                            echo '">
  <input type="hidden" name="item_name" value="Withdraw to ';
                            echo $user['name'];
                            echo ' from ';
                            echo $settings['site_name'];
                            echo '">
  <input type="hidden" name="amount" value="';
                            echo $to_withdraw;
                            echo '">
  <input type="hidden" name="return" value="';
                            echo $settings['site_url'];
                            echo '/paypal_processing.php">
  <input type="hidden" name="cancel_return" value="';
                            echo $frm_env['HTTPS'] ? 'https' : 'http';
                            echo '://".';
                            echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                            echo '."?a=pay_withdraw&say=no">
  <input type=hidden name=custom value="pay_withdraw|';
                            echo $id;
                            echo '-';
                            echo $trans['str'];
                            echo '">
  <input type=hidden name=quantity value=1>
  <input type=hidden name=no_note value=1>
  <input type=hidden name=no_shipping value=1>
  <input type=hidden name=rm value=2>
  <input type=hidden name=currency_code value=USD>
<br><input type=submit value="Go to paypal.com" class=sbmt> &nbsp;
<input type=button class=sbmt value="Cancel" onclick="window.close();">
</form>';
                        } else {
                            if ($trans['ec'] == 7) {
                                echo 'Sending <b>$';
                                echo $to_withdraw;
                                echo '</b> to GoldMoney.<br>
<form action="https://secure.goldmoney.com/omi/omipmt.php" method="post">
<input type=hidden name=withdraw value="';
                                echo $id;
                                echo '-';
                                echo $trans['str'];
                                echo '">
<input type=hidden name=a value="pay_withdraw">
<input type="hidden" name="OMI_MERCHANT_HLD_NO" value="';
                                echo $user['goldmoney_account'];
                                echo '">
<input type="hidden" name="OMI_CURRENCY_AMT" value="';
                                echo $to_withdraw;
                                echo '">
<input type="hidden" name="OMI_CURRENCY_CODE" value="840">
<input type="hidden" name="OMI_MERCHANT_MEMO" value="Withdraw to ';
                                echo $user['name'];
                                echo ' from ';
                                echo $settings['site_name'];
                                echo '">
<input type="hidden" name="OMI_RESULT_URL" value="';
                                echo $settings['site_url'];
                                echo '/goldmoney_processing.php">
<input type="hidden" name="OMI_SUCCESS_URL" value="';
                                echo $frm_env['HTTPS'] ? 'https' : 'http';
                                echo '://';
                                echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                                echo '?a=pay_withdraw&say=yes">
<input type="hidden" name="OMI_SUCCESS_URL_METHOD" value="post">
<input type="hidden" name="OMI_FAIL_URL" value="';
                                echo $frm_env['HTTPS'] ? 'https' : 'http';
                                echo '://';
                                echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                                echo '?a=pay_withdraw&say=no">
<input type="hidden" name="OMI_FAIL_URL_METHOD" value="post">
<br><input type=submit value="Go to goldmoney.com" class=sbmt> &nbsp;
<input type=button class=sbmt value="Cancel" onclick="window.close();">
</form>';
                            } else {
                                if ($trans['ec'] == 8) {
                                    echo 'Sending <b>$';
                                    echo $to_withdraw;
                                    echo '</b> to eeeCurrency.<br>
<form name=spend method=post action="https://eeecurrency.com/cgi-bin/api.cgi">
<input type="hidden" name="cmd" value="start">
<input type=hidden name=CUSTOM1 value="';
                                    echo $id;
                                    echo '-';
                                    echo $trans['str'];
                                    echo '">
<input type=hidden name=CUSTOM2 value="pay_withdraw_eeecurrency">
<INPUT type=hidden name=AMOUNT value="';
                                    echo $to_withdraw;
                                    echo '">
<INPUT type=hidden name=SELLERACCOUNTID value="';
                                    echo $user['eeecurrency_account'];
                                    echo '">
<INPUT type=hidden name=POSTURL value="';
                                    echo $settings['site_url'];
                                    echo '/">
<INPUT type=hidden name=RETURNURL value="';
                                    echo $frm_env['HTTPS'] ? 'https' : 'http';
                                    echo '://';
                                    echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                                    echo '?a=pay_withdraw&say=yes">
<INPUT type=hidden name=CANCEL_RETURN value="';
                                    echo $frm_env['HTTPS'] ? 'https' : 'http';
                                    echo '://';
                                    echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                                    echo '?a=pay_withdraw&say=no">
<input type="hidden" name="METHOD" value="POST">
<input type="hidden" name="RETURNPAGE" value="CGI">
<input type="hidden" name="ITEM_NUMBER" value="1">
<INPUT type=hidden value="Withdraw to ';
                                    echo $user['name'];
                                    echo ' from ';
                                    echo $settings['site_name'];
                                    echo '" name=ITEM_NAME>
<br>
<br><input type=submit value="Go to eeeCurrency.com" class=sbmt> &nbsp;
<input type=button class=sbmt value="Cancel" onclick="window.close();">
</form>';
                                } else {
                                    if ($trans['ec'] == 9) {
                                        echo 'Sending <b>$';
                                        echo $to_withdraw;
                                        echo '</b> to Pecunix.<br>
<form name=spend method=post action="https://pri.pecunix.com/money.refined">
<input type=hidden name=withdraw value="';
                                        echo $id;
                                        echo '-';
                                        echo $trans['str'];
                                        echo '">
<input type=hidden name=a value="pay_withdraw">
<INPUT type=hidden name=PAYMENT_AMOUNT value="';
                                        echo $to_withdraw;
                                        echo '">
<INPUT type=hidden name=PAYEE_ACCOUNT value="';
                                        echo $user['pecunix_account'];
                                        echo '">
<input type="hidden" name="PAYMENT_URL" value="';
                                        echo $frm_env['HTTPS'] ? 'https' : 'http';
                                        echo '://';
                                        echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                                        echo '?a=pay_withdraw&say=yes">
<input type="hidden" name="NOPAYMENT_URL" value="';
                                        echo $frm_env['HTTPS'] ? 'https' : 'http';
                                        echo '://';
                                        echo $frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
                                        echo '?a=pay_withdraw&say=no">
<input type="hidden" name="STATUS_URL" value="';
                                        echo $settings['site_url'];
                                        echo '/pecunix_processing.php">
<input type="hidden" name="STATUS_TYPE" value="FORM">
<input type="hidden" name="PAYMENT_URL_METHOD" value="POST">
<input type="hidden" name="NOPAYMENT_URL_METHOD" value="POST">
<input type="hidden" name="PAYMENT_UNITS" value="USD">
<input type="hidden" name="WHO_PAYS_FEES" value="PAYEE">
<input type="hidden" name="SUGGESTED_MEMO" value="Withdraw to ';
                                        echo $user['name'];
                                        echo ' from ';
                                        echo $settings['site_name'];
                                        echo '">
<br>
<br><input type=submit value="Go to Pecunix.com" class=sbmt> &nbsp;
<input type=button class=sbmt value="Cancel" onclick="window.close();">
</form>
';
                                    } else {
                                        if ($trans['ec'] == 999) {
                                            echo '  ';
                                            if ($frm['confirm'] == 'ok') {
                                                $q = 'delete from hm2_history where id = '.$id;
                                                db_query($q);
                                                $q = 'insert into hm2_history set
        user_id = '.$user['id'].',
        amount = -'.abs($trans['actual_amount']).',
        type = \'withdrawal\',
        description = \'Withdraw processed. wire transfer has been sent\',
        actual_amount = -'.abs($trans['actual_amount']).',
  	ec = 999,
        date = now()
        ';
                                                db_query($q);
                                                echo '  Withdrawal has been processed.
  ';
                                            } else {
                                                echo '  You should send a Wire Transfer to the user bank account and then confirm the
  transaction.<br>
  <form name=spend method=post>
  <input type=hidden name=a value=pay_withdraw>
  <input type=hidden name=id value="';
                                                echo $id;
                                                echo '">
  <input type=hidden name=confirm value=ok>

  <br><input type=submit value="Confirm transaction" class=sbmt> &nbsp;
  <input type=button class=sbmt value="Cancel" onclick="window.close();">

  </form>
  ';
                                            }
                                        } else {
                                            if ($frm['confirm'] == 'ok') {
                                                $q = 'delete from hm2_history where id = '.$id;
                                                db_query($q);
                                                $q = 'insert into hm2_history set
         user_id = '.$user['id'].',
         amount = -'.abs($trans['actual_amount']).',
         type = \'withdrawal\',
         description = \'Withdraw processed\',
         actual_amount = -'.abs($trans['actual_amount']).',
         ec = '.$trans['ec'].',
         date = now()
         ';
                                                db_query($q);
                                                $row = $trans;
                                                $q = 'select * from hm2_users where id = '.$row['user_id'];
                                                $sth = db_query($q);
                                                $userinfo = mysql_fetch_array($sth);
                                                $info = [];
                                                $info['username'] = $userinfo['username'];
                                                $info['name'] = $userinfo['name'];
                                                $info['amount'] = number_format(abs($row['amount']), 2);
                                                $info['currency'] = $exchange_systems[$row['ec']]['name'];
                                                $info['account'] = 'n/a';
                                                $info['batch'] = 'n/a';
                                                send_template_mail('withdraw_user_notification', $userinfo['email'],
                                                    $settings['opt_in_email'], $info);
                                                $q = 'select email from hm2_users where id = 1';
                                                $sth = db_query($q);
                                                $admin_row = mysql_fetch_array($sth);
                                                send_template_mail('withdraw_admin_notification', $admin_row['email'],
                                                    $settings['opt_in_email'], $info);
                                                echo 'Withdrawal has been processed.<br><br>
<form>
<input type=button class=sbmt value="Close" onclick="window.close();">
</form>';
                                            } else {
                                                echo 'You should send <b>$';
                                                echo $to_withdraw;
                                                echo '</b> of ';
                                                echo $exchange_systems[$trans['ec']]['name'];
                                                echo ' to the user\'s account and then confirm this transaction.<br>
<form name=spend method=post>
<input type=hidden name=a value=pay_withdraw>
<input type=hidden name=id value="';
                                                echo $id;
                                                echo '">
<input type=hidden name=confirm value=ok>

<br><input type=submit value="Confirm transaction" class=sbmt> &nbsp;
<input type=button class=sbmt value="Cancel" onclick="window.close();">

</form>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
