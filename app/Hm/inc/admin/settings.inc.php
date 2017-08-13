<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$admin_stat_password = '';
$q = 'select * from hm2_users where id=1';
$sth = db_query($q);
while ($row = mysql_fetch_array($sth)) {
    if ($row['stat_password'] != '') {
        $admin_stat_password = '*****';
        continue;
    }
}

$gpg_version = 0;
$gpg_command = escapeshellcmd($settings['gpg_path']).' --version';
$fp = popen(''.$gpg_command, 'r');
if ($fp) {
    while (!feof($fp)) {
        $buf = fgets($fp, 4096);
        $pos = strstr($buf, 'gpg (GnuPG)');
        if (0 < strlen($pos)) {
            $gpg_version = preg_replace('/[\\n\\r]/', '', substr($pos, 11));
            continue;
        }
    }

    pclose($fp);
}

$settings['md5altphrase_ebullion'] = decode_pass_for_mysql($settings['md5altphrase_ebullion']);

$settings['use_alternative_passphrase'] = ($userinfo['transaction_code'] != '' ? 1 : 0);
if (isset($frm['say']) && $frm['say'] == 'invalid_passphrase') {
    echo '<b style="color:red">Invalid Alternative Passphrase. No data has been updated.</b><br><br>';
}

if (isset($frm['say']) && $frm['say'] == 'done') {
    echo '<b style="color:green">Changes has been successfully saved.</b><br><br>';
}

echo '<s';
echo 'cript language=javascript>
function check_form()
{
  var d = document.mainform;';
if ($settings['use_alternative_passphrase'] == 0) {
    echo '  if (d.use_alternative_passphrase.options[d.use_alternative_passphrase.selectedIndex].value == 1 && d.new_alternative_passphrase.value == \'\')
  {
    alert(\'Please enter your New Alternative Passphrase!\');
    d.new_alternative_passphrase.focus();
    return false;
  }';
}

echo '
  if (d.new_alternative_passphrase.value != \'\' && d.new_alternative_passphrase.value != d.new_alternative_passphrase2.value)
  {
    alert(\'Please, check your Alternative Passphrase!\');
    d.new_alternative_passphrase2.focus();
    return false;
  }
}
</script>

<form method=post name="mainform" enctype="multipart/form-data" onsubmit="return check_form()">
<input type=hidden name=a value';
echo '=settings>
<input type=hidden name=action value=settings>

<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td colspan=2><b>Main settings:</b><br><br></td>
</tr><tr>
 <td>Site name:</td>
 <td><input type=text name=site_name value=\'';
echo quote($settings['site_name']);
echo '\' class=inpts size=30></td>
</tr>
<tr>
 <td>Start day:</td>
 <td>';
echo '<s';
echo 'elect name=site_start_day class=inpts>';
for ($i = 1; $i < 32; ++$i) {
    echo '<option value=';
    echo $i;
    echo ' ';
    echo $i == $settings['site_start_day'] ? 'selected' : '';
    echo '>';
    echo $i;
}

echo '</select>';
echo '<s';
echo 'elect name=site_start_month class=inpts>';
for ($i = 0; $i < count($month); ++$i) {
    echo '<option value=';
    echo $i + 1;
    echo ' ';
    echo $i + 1 == $settings['site_start_month'] ? 'selected' : '';
    echo '>';
    echo $month[$i];
}

echo '</select>';
echo '<s';
echo 'elect name=site_start_year class=inpts>';
for ($i = date('Y') - 6; $i <= date('Y'); ++$i) {
    echo '<option value=';
    echo $i;
    echo ' ';
    echo $i == $settings['site_start_year'] ? 'selected' : '';
    echo '>';
    echo $i;
}

echo '</select>
 </td>
</tr><tr>
<td colspan=2><input type=checkbox name=reverse_columns value=1 ';
echo $settings['reverse_columns'] == 1 ? 'checked' : '';
echo '> Reverse left and right columns</td>
</tr>

<tr>
 <td colspan=2>&nbsp;<br><b>Perfect Money account settings:</b></td>
</tr><tr>
 <td>Your Perfect Money USD account number:</td>
 <td><input type=text name=\'def_payee_account_perfectmoney\' value=\'';
echo quote($settings['def_payee_account_perfectmoney']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Your Perfect Money account name:</td>
 <td><input type=text name=\'def_payee_name_perfectmoney\' value=\'';
echo quote($settings['def_payee_name_perfectmoney']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Secret alternate password md5 hash:</td>
 <td><input type=text name=\'md5altphrase_perfectmoney\' value=\'';
echo quote($settings['md5altphrase_perfectmoney']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Alternate Password:</td>
 <td><input type=text name=\'perfectmoneyap\' class=inpts size=30> <input type=button class=sbmt onclick="document.mainform.md5altphrase_perfectmoney.value = calcMD5(document.mainform.perfectmoneyap.value)" value="Calculate MD5 hash"></td>
</tr>

<tr>
 <td colspan=2>&nbsp;<br><b>Payeer account settings:</b></td>
</tr><tr>
 <td>Your Payeer Merchant’s ID:</td>
 <td><input type=text name=\'def_payee_account_payeer\' value=\'';
echo quote($settings['def_payee_account_payeer']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Your Payeer Secret key:</td>
 <td><input type=text name=\'def_payee_key_payeer\' value=\'';
echo quote($settings['def_payee_key_payeer']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Your Payeer Additional Key:</td>
 <td><input type=text name=\'def_payee_additionalkey_payeer\' value=\'';
echo quote($settings['def_payee_additionalkey_payeer']);
echo '\' class=inpts size=30></td>
</tr>

<tr>
 <td colspan=2>&nbsp;<br><b>BitCoin account settings:</b></td>
</tr><tr>
 <td>Your BitCoin Receive Address:</td>
 <td><input type=text name=\'def_payee_account_bitcoin\' value=\'';
echo quote($settings['def_payee_account_bitcoin']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Your BitCoin QR Code Url:</td>
 <td><input type=text name=\'def_payee_qrcode_bitcoin\' value=\'';
echo quote($settings['def_payee_qrcode_bitcoin']);
echo '\' class=inpts size=30></td>
</tr>

<!--
<tr>
 <td colspan=2>&nbsp;<br><b>E-gold account settings:</b></td>
</tr><tr>
 <td>Your e-gold account number:</td>
 <td><input type=text name=\'def_payee_account\' value=\'';
echo quote($settings['def_payee_account']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Your e-gold account name:</td>
 <td><input type=text name=\'def_payee_name\' value=\'';
echo quote($settings['def_payee_name']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Secret alternate password md5 hash:</td>
 <td><input type=text name=\'md5altphrase\' value=\'';
echo quote($settings['md5altphrase']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Alternate Password:</td>
 <td><input type=text name=\'egoldap\' class=inpts size=30> <input type=button class=sbmt onclick="document.mainform.md5altphrase.value = calcMD5(document.mainform.egoldap.value)" value="Calculate MD5 hash"></td>
</tr>

<tr>
 <td colspan=2>&nbsp;<br><b>
        INTGold settings</b></td>
</tr>';
if (file_exists('intgold_processing.php')) {
    echo '<tr>
      <td colspan=2>
        ';
    echo start_info_table('100%');
    echo '        <b>You have not renamed "intgold_processing.php" file. It is insecure!</b>
        ';
    echo end_info_table();
    echo '      </td>
    </tr>';
}

echo '<tr>
 <td>Your INTGold account number:</td>
 <td><input type=text name=\'def_payee_account_intgold\' value=\'';
echo quote($settings['def_payee_account_intgold']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Password for MD5 verification:</td>
 <td><input type=text name=\'md5altphrase_intgold\' value=\'';
echo quote($settings['md5altphrase_intgold']);
echo '\' class=inpts size=30></td>
</tr><tr>
      <td>INTGold PostUrl number:</td>
 <td><input type=text name=\'intgold_posturl\' value=\'';
echo quote($settings['intgold_posturl']);
echo '\' class=inpts size=30></td>
</tr><tr>
<td colspan=2>&nbsp;<br><b>eeeCurrency settings</b></td>
</tr>';
if (file_exists('eeecurrency_processing.php')) {
    echo '<tr>
      <td colspan=2>
        ';
    echo start_info_table('100%');
    echo '        <b>You have not renamed "eeecurrency_processing.php" file. It is insecure!</b>
        ';
    echo end_info_table();
    echo '      </td>
    </tr>';
}

echo '<tr>
 <td>Your eeeCurrency account number:</td>
 <td><input type=text name=\'def_payee_account_eeecurrency\' value=\'';
echo quote($settings['def_payee_account_eeecurrency']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Password for MD5 verification:</td>
 <td><input type=text name=\'md5altphrase_eeecurrency\' value=\'';
echo quote($settings['md5altphrase_eeecurrency']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>eeeCurrency PostUrl number:</td>
 <td><input type=text name=\'eeecurrency_posturl\' value=\'';
echo quote($settings['eeecurrency_posturl']);
echo '\' class=inpts size=30></td>
</tr>
<tr>
<td colspan=2>&nbsp;<br><b>Pecunix settings</b></td>
</tr>
<tr>
 <td>Your Pecunix account:</td>
 <td><input type=text name=\'def_payee_account_pecunix\' value=\'';
echo quote($settings['def_payee_account_pecunix']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Sdared Secret:</td>
 <td><input type=text name=\'md5altphrase_pecunix\' value=\'';
echo quote($settings['md5altphrase_pecunix']);
echo '\' class=inpts size=30></td>
</tr>
<tr>
 <td colspan=2>&nbsp;<br><b>Stormpay settings</td>
</tr>';
if (file_exists('stormpay_processing.php')) {
    echo '<tr>
      <td colspan=2>
        ';
    echo start_info_table('100%');
    echo '        <b>You have not renamed "stormpay_processing.php" file. It is insecure!</b>
        ';
    echo end_info_table();
    echo '      </td>
    </tr>';
}

echo '<tr>
 <td>Your stormpay account name:</td>
 <td><input type=text name=\'def_payee_account_stormpay\' value=\'';
echo quote($settings['def_payee_account_stormpay']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Secret Code:</td>
 <td><input type=text name=\'md5altphrase_stormpay\' value=\'';
echo quote($settings['md5altphrase_stormpay']);
echo '\' class=inpts size=30></td>
</tr><tr>
      <td>IPN url:</td>
 <td><input type=text name=\'stormpay_posturl\' value=\'';
echo quote($settings['stormpay_posturl']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td colspan=2><input type=checkbox name=dec_stormpay_fee value=1 ';
echo $settings['dec_stormpay_fee'] == 1 ? 'checked' : '';
echo '> Decrease stormpay fee (6.9% plus 0.69)</td>
</tr><tr>
 <td colspan=2>&nbsp;<br><b>e-Bullion settings</td>
</tr><tr>
 <td>GPG Path:</td>
 <td><input type=text name=\'gpg_path\' value=\'';
echo quote($settings['gpg_path']);
echo '\' class=inpts size=30> ';
echo $gpg_version != '' ? 'Version: '.$gpg_version : '';
echo '</td>
</tr><tr>';
if ($gpg_version != '') {
    echo ' <td>Your e-Bullion account ID:</td>
 <td><input type=text name=\'def_payee_account_ebullion\' value=\'';
    echo quote($settings['def_payee_account_ebullion']);
    echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Your e-Bullion account Name:</td>
 <td><input type=text name=\'def_payee_name_ebullion\' value=\'';
    echo quote($settings['def_payee_name_ebullion']);
    echo '\' class=inpts size=30></td>
</tr><tr>
 <td>GPG Passphrase:</td>
 <td><input type=text name=\'md5altphrase_ebullion\' value=\'';
    echo quote($settings['md5altphrase_ebullion']);
    echo '\' class=inpts size=30></td>
</tr><tr>
 <td>GPG key ID:</td>
 <td><input type=text name=\'ebullion_keyID\' value=\'';
    echo quote($settings['ebullion_keyID']);
    echo '\' class=inpts size=30></td>
</tr><tr>
 <td colspan=2>';
    echo start_info_table('100%');
    echo '        Your e-Bullion account number:<br>
        Account to receive deposits. Clear this field to disable e-Bullion deposits.<br>
<br>
        All required data for e-Bullion usage is included in the customized for your account downlodable ATIP SDK.<br>
        To download it please login to your e-Bullion account then click on \'Account Settings\' in the left menu and then on \'ATIP Settings\' in t';
    echo 'he newly opened window. In the bottom of the last page you will see the link \'ATIP SDK Download: (Customized for BXXXXXX)\'. Click it to download SDK archive.<br>
        Unpack the archive onto your local computer and choose the next files in the fields below:<br>
        <table cellspacing=0 cellpadding=2 border=0>
         <tr><td>atip.pl :</td><td><input type=file name=atip_pl class=inpts></td><td>';
    echo ($settings['def_payee_account_ebullion'] and $settings['md5altphrase_ebullion']) ? '<b style="color: green">OK</b>' : '<b style="color: red">NO</b>';
    echo '</td></tr>
         <tr><td>status.php :</td><td><input type=file name=status_php class=inpts></td><td>';
    echo $settings['ebullion_keyID'] ? '<b style="color: green">OK</b>' : '<b style="color: red">NO</b>';
    echo '</td></tr>
         <tr><td>pubring.gpg :</td><td><input type=file name=pubring_gpg class=inpts></td><td>';
    echo is_file(CACHE_PATH.'/pubring.gpg') ? '<b style="color: green">OK</b>' : '<b style="color: red">NO</b>';
    echo '</tr>
         <tr><td>secring.gpg :</td><td><input type=file name=secring_gpg class=inpts></td><td>';
    echo is_file(CACHE_PATH.'/secring.gpg') ? '<b style="color: green">OK</b>' : '<b style="color: red">NO</b>';
    echo '</tr>
        </table>
        then save settings. The system will parse the selected files and will get the required information which you will see in the fields above. You will have to enter your e-Bullion account name then.<br><br>
<br><br>
<input value="Test e-bullion" type="button" onclick="window.open(\'?a=test_ebullion_settings\', \'_testebullion\', \'width=400, height=200, status=0\');" class=sbmt>';
    echo '<br>
e-bullion processing works if your can see your balance after pressing the "Test" button.
<br><br>

        <b>login as user and try to deposit.</b><br>';
    echo end_info_table();
    echo ' </td>
</tr><tr>';
} else {
    echo ' <td colspan=2>';
    echo start_info_table('100%');
    echo 'To use e-Bullion payment system in automatical mode you must have the GPG (GnuPG) installed on your server and know the full path to it.<br>
If you do not know whether you have the GPG installed on your server please contact your hosting provider.<br>
After you obtain the path to the GPG program place it in the field above and save settings. You will see more fields for e-Bullion data.';
    echo end_info_table();
    echo ' </td>
</tr><tr>';
}

if (function_exists('curl_init')) {
    echo ' <td colspan=2>&nbsp;<br><b>PayPal account settings:</b></td>
</tr><tr>
 <td>Your PayPal account e-mail:</td>
 <td><input type=text name=\'def_payee_account_paypal\' value=\'';
    echo quote($settings['def_payee_account_paypal']);
    echo '\' class=inpts size=30></td>
</tr><tr>
      <td colspan=2>
        ';
    echo start_info_table('100%');
    echo '        Specify your PayPal account settings for income transfers here. Clear
        this field to disable PayPal deposits.<br>
        <br>
        <b>login as a user and try to deposit to test settings.</b><br>
        ';
    echo end_info_table();
    echo '</td>

        </tr><tr>';
}

echo ' <td colspan=2>&nbsp;<br><b>GoldMoney account settings:</b></td>
</tr><tr>
 <td>Your GoldMoney Holding Number:</td>
 <td><input type=text name=\'def_payee_account_goldmoney\' value=\'';
echo quote($settings['def_payee_account_goldmoney']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Your GoldMoney Secret Key:</td>
 <td><input type=text name=\'md5altphrase_goldmoney\' value=\'';
echo quote($settings['md5altphrase_goldmoney']);
echo '\' class=inpts size=30></td>
</tr>
-->
<tr>
 <td colspan=2>&nbsp;<br>
        <b>Administrator login settings:</b></td>
</tr><tr>
 <td>Login:</td>
 <td><input type=text name=admin_login value=\'';
echo quote($userinfo['username']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Password:</td>
 <td><input type=password name=admin_password value=\'\' class=inpts size=30></td>
</tr><tr>
 <td>Retype password:</td>
 <td><input type=password name=admin_password2 value=\'\' class=inpts size=30></td>
</tr><tr>
 <td>Administrator e-mail:</td>
 <td><input type=text name=admin_email value=\'';
echo quote($userinfo['email']);
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Password for Win program:</td>
 <td><input type=text name=admin_stat_password value=\'';
echo $admin_stat_password;
echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Retype password for Win program:</td>
 <td><input type=text name=admin_stat_password2 value=\'';
echo $admin_stat_password;
echo '\' class=inpts size=30></td>
</tr><tr>
      <td colspan=2>
        ';
echo start_info_table('100%');
echo '        Administrator login settings: type a new login and a password here to login
        the admin area.<br>
        Password for Win program: you can use a windows system tray add-on. Type
        a password for this program in this field.<br>
        Do not use the same password for the admin area and for the system tray add-on!
        ';
echo end_info_table();
echo '</td>
    </tr><tr>
 <td colspan=2>&nbsp;<br><b>Other settings:</b></td>
</tr>
<tr>
 <td>Deny registrations:</td>
 <td>';
echo '<s';
echo 'elect name=deny_registration class=inpts><option value=1 ';
echo $settings['deny_registration'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['deny_registration'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr>
<tr>
 <td>Double opt-in during registration:</td>
 <td>';
echo '<s';
echo 'elect name=use_opt_in class=inpts><option value=1 ';
echo $settings['use_opt_in'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['use_opt_in'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr>
<tr>
 <td>Opt-in e-mail:</td>
 <td><input type=text name=opt_in_email value=\'';
echo quote($settings['opt_in_email']);
echo '\' class=inpts size=30>
</tr>
<tr>
 <td>Use user location fields:</td>
 <td>';
echo '<s';
echo 'elect name=use_user_location class=inpts><option value=1 ';
echo $settings['use_user_location'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['use_user_location'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr>
<tr>
 <td>Minimal user password length:</td>
 <td><input type=text name=min_user_password_length value=\'';
echo quote($settings['min_user_password_length']);
echo '\' class=inpts size=6>
</tr>
<tr>
 <td>System e-mail:</td>
 <td><input type=text name=system_email value=\'';
echo quote($settings['system_email']);
echo '\' class=inpts size=30>
</tr><tr>
 <td>Enable Calculator:</td>
 <td>';
echo '<s';
echo 'elect name=enable_calculator class=inpts><option value=1 ';
echo $settings['enable_calculator'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['enable_calculator'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr><tr>
 <td>Use double entry accounting:</td>
 <td>';
echo '<s';
echo 'elect name=use_history_balance_mode class=inpts><option value=1 ';
echo $settings['use_history_balance_mode'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['use_history_balance_mode'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr><tr>
 <td>Redirect to HTTPS:</td>
 <td>
  <table cellspacing=0 cellpadding=0 border=0><tr>
   <td>';
echo '<s';
echo 'elect name=redirect_to_https class=inpts><option value=1 ';
echo $settings['redirect_to_https'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['redirect_to_https'] == 0 ? 'selected' : '';
echo '>No</select></td>
   <td style="padding-left:5px">';
echo '<s';
echo 'mall>Do not change this option if you don\'t exactly know how does it work.</small></td></tr></table>
 </td>
</tr><tr>
 <td>Withdrawal Fee (%):</td>
 <td><input type=text name=withdrawal_fee value=\'';
echo quote($settings['withdrawal_fee']);
echo '\' class=inpts size=6></td>
</tr><tr>
 <td>Minimal Withdrawal Fee ($):</td>
 <td><input type=text name=withdrawal_fee_min value=\'';
echo quote($settings['withdrawal_fee_min']);
echo '\' class=inpts size=6></td>
</tr><tr>
 <td>Minimal Withdrawal Amount ($):</td>
 <td><input type=text name=min_withdrawal_amount value=\'';
echo quote($settings['min_withdrawal_amount']);
echo '\' class=inpts size=6></td>
</tr>
      <td colspan=2>
        ';
echo start_info_table('100%');
echo '        Double opt-in when registering: Select \'yes\' if a user has to confirm the
        registration. An E-mail with the confirmation code will be sent to the user
        after he had submitted the registration request.<br>
Opt-in e-mail: Confirmation messages will be sent from this e-mail account.<br>
System e-mail: All system messages will be sent from this e-mail account.<br>
        Use';
echo ' user location fields: Adds &quot;Address&quot;, &quot;City&quot;,
        &quot;State&quot;, &quot;Zip&quot; and &quot;Country&quot; fields to user\'s
        profile.<br>
        Min user password length: Specifies the minimal user password and the
        transaction code length.<br>
		Use double entry accounting: This mod is used for the transactions history screen in both users and admin areas.';
echo end_info_table();
echo '</td>
</tr><tr>
 <td colspan=2>&nbsp;<br><b>User settings:</b></td>
</tr><tr>
      <td>Users can use the WAP access:</td>
 <td>';
echo '<s';
echo 'elect name=usercanaccesswap class=inpts><option value=1 ';
echo $settings['accesswap'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['accesswap'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr><tr>
 <td>Users should use a transaction code to withdraw:</td>
 <td>';
echo '<s';
echo 'elect name=use_transaction_code class=inpts><option value=1 ';
echo $settings['use_transaction_code'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['use_transaction_code'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr><tr>
 <td>Use confirmation code when account update:</td>
 <td>';
echo '<s';
echo 'elect name=account_update_confirmation class=inpts><option value=1 ';
echo $settings['account_update_confirmation'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['account_update_confirmation'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr>

<tr>
      <td>Change e-gold account:</td>
 <td>';
echo '<s';
echo 'elect name=usercanchangeegoldacc class=inpts><option value=1 ';
echo $settings['usercanchangeegoldacc'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['usercanchangeegoldacc'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr>

<tr>
      <td>Change Perfect Money account:</td>
 <td>';
echo '<s';
echo 'elect name=usercanchangeperfectmoneyacc class=inpts><option value=1 ';
echo $settings['usercanchangeperfectmoneyacc'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['usercanchangeperfectmoneyacc'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr>

<tr>
 <td>Change e-mail:</td>
 <td>';
echo '<s';
echo 'elect name=usercanchangeemail class=inpts><option value=1 ';
echo $settings['usercanchangeemail'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['usercanchangeemail'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr><tr>
      <td>Notify user of his profile change:</td>
 <td>';
echo '<s';
echo 'elect name=sendnotify_when_userinfo_changed class=inpts><option value=1 ';
echo $settings['sendnotify_when_userinfo_changed'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['sendnotify_when_userinfo_changed'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr><tr>
 <td>Allow internal transfer:</td>
 <td>';
echo '<s';
echo 'elect name=internal_transfer_enabled class=inpts><option value=1 ';
echo $settings['internal_transfer_enabled'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['internal_transfer_enabled'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr><tr>
 <td>Allow Deposit to Account:</td>
 <td>';
echo '<s';
echo 'elect name=use_add_funds class=inpts><option value=1 ';
echo $settings['use_add_funds'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['use_add_funds'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr><tr>
 <td>Max daily withdraw:</td>
 <td><input type=text name=max_daily_withdraw class=inpts value=\'';
echo sprintf('%0.2f', $settings['max_daily_withdraw']);
echo '\' style=\'text-align: right\'> ';
echo '<s';
echo 'mall>(0 for unlimited)</small></td>
</tr><tr>
      <td colspan=2>
        ';
echo start_info_table('100%');
echo '        Here you can specify whether user can change his own e-gold or e-mail
        account after registration.<br>
        Also system can send e-mail to user when he changes his profile (for security
        reason).<br>
        Users should use transaction code to withdraw: Specifies an additional
        password which is needed to do the withdrawal. That password can be restored
   ';
echo '     by the administrator only. It is stored in MySQL database in plain format.
        ';
echo end_info_table();
echo '      </td>
    </tr><tr>
 <td>&nbsp;<br><b>Turing verification:</b></td>
</tr><tr>
 <td>Use turing verification:</td>
 <td>';
echo '<s';
echo 'elect name=graph_validation class=inpts><option value=1 ';
echo $settings['graph_validation'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['graph_validation'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr><tr>
      <td>Number of characters in the turing image:</td>
 <td><input type=text name=graph_max_chars value="';
echo $settings['graph_max_chars'];
echo '" class=inpts size=10></td>
</tr><tr>
 <td colspan=2><input type=checkbox name=use_number_validation_number value=1 ';
echo $settings['use_number_validation_number'] == 1 ? 'checked' : '';
echo '> Show numbers only in the validation image</td>
</tr><tr>
 <td>Turing image text color:</td>
 <td><input type=text name=graph_text_color value="';
echo $settings['graph_text_color'];
echo '" class=inpts size=10></td>
</tr><tr>
 <td>Turing image bg color:</td>
 <td><input type=text name=graph_bg_color value="';
echo $settings['graph_bg_color'];
echo '" class=inpts size=10></td>
</tr>';
if ((function_exists('imagettfbbox') or $settings['demomode'] == 1)) {
    echo '<tr>
 <td>Use advanced turing verification:</td>
 <td>';
    echo '<s';
    echo 'elect name=advanced_graph_validation class=inpts><option value=1 ';
    echo $settings['advanced_graph_validation'] == 1 ? 'selected' : '';
    echo '>Yes<option value=0 ';
    echo $settings['advanced_graph_validation'] == 0 ? 'selected' : '';
    echo '>No</select></td>
</tr>
<tr>
 <td>Font minimal size:</td>
 <td><input type=text name=advanced_graph_validation_min_font_size value="';
    echo $settings['advanced_graph_validation_min_font_size'];
    echo '" class=inpts size=10></td>
</tr>
<tr>
 <td>Font maximal size:</td>
 <td><input type=text name=advanced_graph_validation_max_font_size value="';
    echo $settings['advanced_graph_validation_max_font_size'];
    echo '" class=inpts size=10></td>
</tr>';
}

echo '<tr>
      <td colspan=2>
        ';
echo start_info_table('100%');
echo '        You can use the turing image for verification when users login to the system.
        It will stop brute force scripts from hacking passwords.<br>
        Change the text and background color of the turing image here.<br>
        Use advanced turing verification: Creates a turing image with the font
        \'fonts/font.ttf\' (you can upload any TTF font into this file). The font
     ';
echo '   size (in a range specified in &quot;Font min size&quot; and &quot;Font
        max size&quot;) and angle are random for each char. White noise is added
        into the final image.
        ';
echo end_info_table();
echo '      </td>
    </tr>
<tr>
 <td>&nbsp;<br><b>Brute force handler:</b></td>
</tr><tr>
 <td>Prevent brute force:</td>
 <td>';
echo '<s';
echo 'elect name=brute_force_handler class=inpts><option value=1 ';
echo $settings['brute_force_handler'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['brute_force_handler'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr><tr>
 <td>Max invalid tries:</td>
 <td><input type=text name=brute_force_max_tries value="';
echo $settings['brute_force_max_tries'];
echo '" class=inpts size=10></td>
</tr><tr>
      <td colspan=2>
        ';
echo start_info_table('100%');
echo '        Prevent brute force: Turns on the brute force prevention system.<br>
        Max invalid tries: The number of invalid login tries. The login is being
        blocked if one tries to login more than specified here number of times
        with the invalid password. The e-mail message with an activation link
        is generated and being sent to a user. One cannot login even with a cor';
echo 'rect
        password before the account activation.
        ';
echo end_info_table();
echo '      </td>
    </tr><tr>
 <td>&nbsp;</td>
</tr><tr>
 <td>&nbsp;<br><b>Time settings:</b></td>
</tr><tr>
 <td>Server time:</td>
 <td>';
echo date('dS of F Y h:i:s A');
echo '</td>
</tr><tr>
 <td>System time:</td>
 <td>';
echo date('dS of F Y h:i:s A', time() + $settings['time_dif'] * 60 * 60);
echo '</td>
</tr><tr>
 <td>Difference:</td>
 <td><input type=text name=time_dif value="';
echo $settings['time_dif'];
echo '" class=inpts size=10> hours</td>
</tr><tr>
      <td colspan=2>
        ';
echo start_info_table('100%');
echo '        Change your system time. You can set the system to show all dates for
        your time zone.
        ';
echo end_info_table();
echo '</td>
    </tr><tr>
 <td>&nbsp;</td>
</tr>
<tr>
 <td colspan=2>&nbsp;<br><b>Administrator Alternative Passphrase:</b></td>
</tr><tr>
<tr>
 <td>Use admin alternative passphrase:</td>
 <td>';
echo '<s';
echo 'elect name=use_alternative_passphrase class=inpts><option value=1 ';
echo $settings['use_alternative_passphrase'] == 1 ? 'selected' : '';
echo '>Yes<option value=0 ';
echo $settings['use_alternative_passphrase'] == 0 ? 'selected' : '';
echo '>No</select></td>
</tr>
<tr>
 <td>New alternative passphrase:</td>
 <td><input type=password name=new_alternative_passphrase value="" class=inpts size=30></td>
</tr>
<tr>
 <td>Confirm New alternative passphrase:</td>
 <td><input type=password name=new_alternative_passphrase2 value="" class=inpts size=30></td>
</tr>
<tr>
      <td colspan=2>
        ';
echo start_info_table('100%');
echo '        This feature raises the security level for the administrator area. If
        enabled Administrator can change \'Settings\', \'Auto-Withdrawal Settings\'
        and \'Security\' properties knowing the Alternative Passphrase only.
        ';
echo end_info_table();
echo '      </td>
</tr>
<tr>
 <td>&nbsp;</td>
</tr>';
if ($settings['use_alternative_passphrase']) {
    echo '<tr>
 <td>Alternative Passphrase: </td>
 <td><input type=password name="alternative_passphrase" value="" class=inpts size=30></td>
</tr>';
}

echo '<tr>
 <td>&nbsp;</td>
 <td><input type=submit value="Change settings" class=sbmt></td>
</tr></table>
</form>';
echo '<s';
echo 'cript language="JavaScript">
<!--
/* jrw note: this md5 code GPL\'d by paul johnston at his web site: http://cw.oaktree.co.uk/site/legal.html */
/*
	** pjMd5.js
	**
	** A JavaScript implementation of the RSA Data Security, Inc. MD5
	** Message-Digest Algorithm.
	**
	** Copyright (C) Paul Johnston 1999.
	*/

	var sAscii=" !\\"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\\\]^_`"';
echo '
	var sAscii=sAscii+"abcdefghijklmnopqrstuvwxyz{|}~";
	var sHex="0123456789ABCDEF";

	function hex(i) {
	h="";
	for(j=0; j<=3; j++) {
	  h+=sHex.charAt((i>>(j*8+4))&0x0F)+sHex.charAt((i>>(j*8))&0x0F);
	}
	return h;
	}
	function add(x,y) {
	return ((x&0x7FFFFFFF)+(y&0x7FFFFFFF) )^(x&0x80000000)^(y&0x80000000);
	}
	function R1(A,B,C,D,X,S,T) {
	q=add( add(A,(B&C)|((~B)&D)), add(X,T) );';
echo '
	return add( (q';
echo '<';
echo '<S';
echo ')|( (q>>(32-S))&(Math.pow(2,S)-1) ), B );
	}
	function R2(A,B,C,D,X,S,T) {
	q=add( add(A,(B&D)|(C&(~D))), add(X,T) );
	return add( (q';
echo '<';
echo '<S';
echo ')|( (q>>(32-S))&(Math.pow(2,S)-1) ), B );
	}
	function R3(A,B,C,D,X,S,T) {
	q=add( add(A,B^C^D), add(X,T) );
	return add( (q';
echo '<';
echo '<S';
echo ')|( (q>>(32-S))&(Math.pow(2,S)-1) ), B );
	}
	function R4(A,B,C,D,X,S,T) {
	q=add( add(A,C^(B|(~D))), add(X,T) );
	return add( (q';
echo '<';
echo '<S';
echo ')|( (q>>(32-S))&(Math.pow(2,S)-1) ), B );
	}

	function calcMD5(sInp) {

	/* Calculate length in words, including padding */
	wLen=(((sInp.length+8)>>6)+1)';
echo '<';
echo '<4;
	var X = new Array(wLen);

	/* Convert string to array of words */
	j=4;
	for (i=0; (i*4)';
echo '<s';
echo 'Inp.length; i++) {
	X[i]=0;
	for (j=0; j<4 && (i*4+j)';
echo '<s';
echo 'Inp.length; j++) {
	  X[i]+=(sAscii.indexOf(sInp.charAt((i*4)+j))+32)';
echo '<';
echo '<(j*8);
	}
	}

	/* Append the 1 and 0s to make a multiple of 4 bytes */
	if (j==4) { X[i++]=0x80; }
	else { X[i-1]+=0x80';
echo '<';
echo '<(j*8); }
	/* Appends 0s to make a 14+k16 words */
	while ( i<wLen ) { X[i]=0; i++; }
	/* Append length */
	X[wLen-2]=sInp.length';
echo '<';
echo '<3;
	/* Initialize a,b,c,d */
	a=0x67452301; b=0xefcdab89; c=0x98badcfe; d=0x10325476;

	/* Process each 16 word block in turn */
	for (i=0; i<wLen; i+=16) {
	aO=a; bO=b; cO=c; dO=d;

	a=R1(a,b,c,d,X[i+ 0],7 ,0xd76aa478);
	d=R1(d,a,b,c,X[i+ 1],12,0xe8c7b756);
	c=R1(c,d,a,b,X[i+ 2],17,0x242070db);
	b=R1(b,c,d,a,X[i+ 3],22,0xc1bdceee);
	a=R1(a,b,c,d,X[i+ 4],7 ,0xf57c0faf);
	d=R1(d,a,b,c,X';
echo '[i+ 5],12,0x4787c62a);
	c=R1(c,d,a,b,X[i+ 6],17,0xa8304613);
	b=R1(b,c,d,a,X[i+ 7],22,0xfd469501);
	a=R1(a,b,c,d,X[i+ 8],7 ,0x698098d8);
	d=R1(d,a,b,c,X[i+ 9],12,0x8b44f7af);
	c=R1(c,d,a,b,X[i+10],17,0xffff5bb1);
	b=R1(b,c,d,a,X[i+11],22,0x895cd7be);
	a=R1(a,b,c,d,X[i+12],7 ,0x6b901122);
	d=R1(d,a,b,c,X[i+13],12,0xfd987193);
	c=R1(c,d,a,b,X[i+14],17,0xa679438e);
	b=R1(b,c,d,a,X[i+15],22,';
echo '0x49b40821);

	a=R2(a,b,c,d,X[i+ 1],5 ,0xf61e2562);
	d=R2(d,a,b,c,X[i+ 6],9 ,0xc040b340);
	c=R2(c,d,a,b,X[i+11],14,0x265e5a51);
	b=R2(b,c,d,a,X[i+ 0],20,0xe9b6c7aa);
	a=R2(a,b,c,d,X[i+ 5],5 ,0xd62f105d);
	d=R2(d,a,b,c,X[i+10],9 , 0x2441453);
	c=R2(c,d,a,b,X[i+15],14,0xd8a1e681);
	b=R2(b,c,d,a,X[i+ 4],20,0xe7d3fbc8);
	a=R2(a,b,c,d,X[i+ 9],5 ,0x21e1cde6);
	d=R2(d,a,b,c,X[i+14],9 ,0xc33707';
echo 'd6);
	c=R2(c,d,a,b,X[i+ 3],14,0xf4d50d87);
	b=R2(b,c,d,a,X[i+ 8],20,0x455a14ed);
	a=R2(a,b,c,d,X[i+13],5 ,0xa9e3e905);
	d=R2(d,a,b,c,X[i+ 2],9 ,0xfcefa3f8);
	c=R2(c,d,a,b,X[i+ 7],14,0x676f02d9);
	b=R2(b,c,d,a,X[i+12],20,0x8d2a4c8a);

	a=R3(a,b,c,d,X[i+ 5],4 ,0xfffa3942);
	d=R3(d,a,b,c,X[i+ 8],11,0x8771f681);
	c=R3(c,d,a,b,X[i+11],16,0x6d9d6122);
	b=R3(b,c,d,a,X[i+14],23,0xfde5380c);
	a';
echo '=R3(a,b,c,d,X[i+ 1],4 ,0xa4beea44);
	d=R3(d,a,b,c,X[i+ 4],11,0x4bdecfa9);
	c=R3(c,d,a,b,X[i+ 7],16,0xf6bb4b60);
	b=R3(b,c,d,a,X[i+10],23,0xbebfbc70);
	a=R3(a,b,c,d,X[i+13],4 ,0x289b7ec6);
	d=R3(d,a,b,c,X[i+ 0],11,0xeaa127fa);
	c=R3(c,d,a,b,X[i+ 3],16,0xd4ef3085);
	b=R3(b,c,d,a,X[i+ 6],23, 0x4881d05);
	a=R3(a,b,c,d,X[i+ 9],4 ,0xd9d4d039);
	d=R3(d,a,b,c,X[i+12],11,0xe6db99e5);
	c=R3(c,d,a,';
echo 'b,X[i+15],16,0x1fa27cf8);
	b=R3(b,c,d,a,X[i+ 2],23,0xc4ac5665);

	a=R4(a,b,c,d,X[i+ 0],6 ,0xf4292244);
	d=R4(d,a,b,c,X[i+ 7],10,0x432aff97);
	c=R4(c,d,a,b,X[i+14],15,0xab9423a7);
	b=R4(b,c,d,a,X[i+ 5],21,0xfc93a039);
	a=R4(a,b,c,d,X[i+12],6 ,0x655b59c3);
	d=R4(d,a,b,c,X[i+ 3],10,0x8f0ccc92);
	c=R4(c,d,a,b,X[i+10],15,0xffeff47d);
	b=R4(b,c,d,a,X[i+ 1],21,0x85845dd1);
	a=R4(a,b,c,d,X[i+ 8';
echo '],6 ,0x6fa87e4f);
	d=R4(d,a,b,c,X[i+15],10,0xfe2ce6e0);
	c=R4(c,d,a,b,X[i+ 6],15,0xa3014314);
	b=R4(b,c,d,a,X[i+13],21,0x4e0811a1);
	a=R4(a,b,c,d,X[i+ 4],6 ,0xf7537e82);
	d=R4(d,a,b,c,X[i+11],10,0xbd3af235);
	c=R4(c,d,a,b,X[i+ 2],15,0x2ad7d2bb);
	b=R4(b,c,d,a,X[i+ 9],21,0xeb86d391);

	a=add(a,aO); b=add(b,bO); c=add(c,cO); d=add(d,dO);
	}
	return hex(a)+hex(b)+hex(c)+hex(d);
	}

//--';
echo '>
</script>';
