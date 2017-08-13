<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include app_path('Hm').'/lib/config.inc.php';

if ($frm['a'] == 'pay_withdraw') {
    $batch = $frm['ATIP_TRANSACTION_ID'];
    list($id, $str) = explode('-', $frm['withdraw']);
    if ($str == '') {
        $str = 'abcdef';
    }

    $str = quote($str);
    $q = 'select * from hm2_history where id = '.$id.' and str = \''.$str.'\' and type=\'withdraw_pending\'';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $q = 'delete from hm2_history where id = '.$id;
        db_query($q);
        $q = 'insert into hm2_history set
          	user_id = '.$row['user_id'].',
          	amount = -'.abs($row['amount']).(''.',
          	type = \'withdrawal\',
          	description = \'Withdraw processed. Batch id = '.$batch.'\',
          	actual_amount = -').abs($row['amount']).',
          	ec = 5,
          	date = now()
  	';
        db_query($q);
        $q = 'select * from hm2_users where id = '.$row['user_id'];
        $sth = db_query($q);
        $userinfo = mysql_fetch_array($sth);
        if ($settings['withdraw_user_notification']) {
            $info = [];
            $info['username'] = $userinfo['username'];
            $info['name'] = $userinfo['name'];
            $info['amount'] = abs($row['amount']);
            $info['currency'] = $exchange_systems[$row['ec']]['name'];
            $info['account'] = $userinfo['ebullion_account'];
            $info['batch'] = $batch;
            send_template_mail('withdraw_user_notification', $userinfo['email'], $settings['opt_in_email'], $info);
        }

        if ($settings['withdraw_admin_notification']) {
            $q = 'select email from hm2_users where id = 1';
            $sth = db_query($q);
            $admin_row = mysql_fetch_array($sth);
            $info = [];
            $info['username'] = $userinfo['username'];
            $info['name'] = $userinfo['name'];
            $info['amount'] = abs($row['amount']);
            $info['currency'] = $exchange_systems[$row['ec']]['name'];
            $info['account'] = $userinfo['ebullion_account'];
            $info['batch'] = $batch;
            send_template_mail('withdraw_admin_notification', $admin_row['email'], $settings['opt_in_email'], $info);
            continue;
        }
    }

    echo 1;
    exit();
}

$gpg_path = escapeshellcmd($settings['gpg_path']);
$atippath = storage_path('tmpl_c');
$passphrase = decode_pass_for_mysql($settings['md5altphrase_ebullion']);
$xmlfile = tempnam('', 'xml.cert.');
$tmpfile = tempnam('', 'xml.tmp.');
$fd = fopen(''.$tmpfile, 'w');
fwrite($fd, $frm_orig['ATIP_VERIFICATION']);
fclose($fd);
$gpg_options = ' --yes --no-tty --no-secmem-warning --no-options --no-default-keyring --batch --homedir '.$atippath.' --keyring=pubring.gpg --secret-keyring=secring.gpg --armor --passphrase-fd 0';
$gpg_command = 'echo \''.$passphrase.'\' | '.$gpg_path.' '.$gpg_options.' --output '.$xmlfile.' --decrypt '.$tmpfile.' 2>&1';
$buf = '';
$keyID = '';
$fp = @popen(''.$gpg_command, 'r');
if (!$fp) {
    echo 'GPG not found';
    exit();
}

while (!feof($fp)) {
    $buf = fgets($fp, 4096);
    $pos = strstr($buf, 'key ID');
    if (0 < strlen($pos)) {
        $keyID = preg_replace('/[\\n\\r]/', '', substr($pos, 7));
        continue;
    }
}

pclose($fp);
if (($keyID == $settings['ebullion_keyID'] and $exchange_systems[5]['status'] == 1)) {
    if (is_file(''.$xmlfile)) {
        $fx = fopen(''.$xmlfile, 'r');
        $xmlcert = fread($fx, filesize(''.$xmlfile));
        fclose($fx);
    }

    $data = parsexml($xmlcert);
    $frm = array_merge($frm, $data);
    $user_id = sprintf('%d', $frm['userid']);
    $h_id = sprintf('%d', $frm['hyipid']);
    $compound = sprintf('%d', $frm['compound']);
    $amount = $frm['amount'];
    $batch = $frm['batch'];
    $account = $frm['payer'];
    $mode = $frm['a'];
    if (($frm['metal'] == 1 and $frm['unit'] == 1)) {
        add_deposit(5, $user_id, $amount, $batch, $account, $h_id, $compound);
    }
}

echo '1';
unlink($tmpfile);
unlink($xmlfile);
