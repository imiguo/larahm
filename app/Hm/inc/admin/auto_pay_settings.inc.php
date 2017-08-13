<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$q = 'select * from hm2_pay_settings where n=\'egold_account_password\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $egold_password = $row['v'];
  }

  $q = 'select * from hm2_pay_settings where n=\'intgold_password\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $intgold_password = $row['v'];
  }

  $q = 'select * from hm2_pay_settings where n=\'intgold_transaction_code\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $intgold_transaction_code = $row['v'];
  }

  $q = 'select * from hm2_pay_settings where n=\'eeecurrency_password\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $eeecurrency_password = $row['v'];
  }

  $q = 'select * from hm2_pay_settings where n=\'eeecurrency_transaction_code\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $eeecurrency_transaction_code = $row['v'];
  }

  $q = 'select * from hm2_pay_settings where n=\'pecunix_password\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $pecunix_password = $row['v'];
  }

  if ($settings['demomode'] == 1) {
      echo start_info_table('100%');
      echo '<b>Demo version restriction</b><br>
You cannot edit these settings.<br>
<br>
Note: This screen is available in Pro version only!!! ';
      echo end_info_table();
  }

  if ($frm['say'] == 'invalid_passphrase') {
      echo '<b style="color:red">Invalid Alternative Passphrase. No data has been updated.</b><br><br>';
  }

  if ($frm['say'] == 'done') {
      echo '<b style="color:green">Changes has been successfully made.</b><br>
<br>';
  }

  if ($settings['demomode'] != 1) {
      echo start_info_table('100%');
      echo '<b>We recommend to use the auto-payment feature only on the dedicated servers. Virtual Shared Hosting has much less security.
<br>Use Mass Payment tool instead <a href=?a=thistory&ttype=withdraw_pending>here</a>.</b>';
      echo end_info_table();
      echo '<br>';
  }

  echo '<s';
  echo 'cript language=javascript>
function test_egold() {';
  if ($settings['demomode'] == 1) {
      echo '  alert("Sorry, not available in demo mode");
  return false;';
  }

  if (!function_exists('curl_init')) {
      echo '  alert("Sorry, curl extension is not installed on server";
  return false;';
  }

  echo '
  if (document.formsettings.egold_from_account == \'\') {
    alert("Please type e-gold account no");
    return false;
  }
  if (document.formsettings.egold_account_password.value == \'\') {
    alert("Please type password");
    return false;
  }
  window.open(\'\', \'testegold\', \'width=400, height=200, status=0\');
  document.testsettings.target = \'testegold\';
  document.testsettings.a.valu';
  echo 'e = \'test_egold_settings\';
  document.testsettings.acc.value = document.formsettings.egold_from_account.value;
  document.testsettings.pass.value = document.formsettings.egold_account_password.value;
  document.testsettings.submit();

}
';
  echo '
function test_intgold() {';
  if ($settings['demomode'] == 1) {
      echo '  alert("Sorry, not available in demo mode");
  return false;';
  }

  if (!function_exists('curl_init')) {
      echo '  alert("Sorry, curl extension is not installed on server";
  return false;';
  }

  echo '
  if (document.formsettings.intgold_from_account.value == \'\') {
    alert("Please type IntGold account no");
    return false;
  }
  if (document.formsettings.intgold_password.value == \'\') {
    alert("Please type IntGold password");
    return false;
  }
  if (document.formsettings.intgold_transaction_code.value == \'\') {
    alert("Please type IntGold secondary password");
    return ';
  echo 'false;
  }

  window.open(\'\', \'testintgold\', \'width=400, height=200, status=0\');
  document.testsettings.target = \'testintgold\';
  document.testsettings.a.value = \'test_intgold_settings\';
  document.testsettings.acc.value = document.formsettings.intgold_from_account.value;
  document.testsettings.pass.value = document.formsettings.intgold_password.value;
  document.testsettings.code.value ';
  echo '= document.formsettings.intgold_transaction_code.value;
  document.testsettings.submit();
}

function test_eeecurrency() {';
  if ($settings['demomode'] == 1) {
      echo '  alert("Sorry, not available in demo mode");
  return false;';
  }

  if (!function_exists('curl_init')) {
      echo '  alert("Sorry, curl extension is not installed on server";
  return false;';
  }

  echo '
  if (document.formsettings.eeecurrency_from_account.value == \'\') {
    alert("Please type eeeCurrency account no");
    return false;
  }
  if (document.formsettings.eeecurrency_password.value == \'\') {
    alert("Please type eeeCurrency password");
    return false;
  }
  if (document.formsettings.eeecurrency_transaction_code.value == \'\') {
    alert("Please type eeeCurrency secondary ';
  echo 'password");
    return false;
  }

  window.open(\'\', \'testeeecurrency\', \'width=400, height=200, status=0\');
  document.testsettings.target = \'testeeecurrency\';
  document.testsettings.a.value = \'test_eeecurrency_settings\';
  document.testsettings.acc.value = document.formsettings.eeecurrency_from_account.value;
  document.testsettings.pass.value = document.formsettings.eeecurrency_password';
  echo '.value;
  document.testsettings.code.value = document.formsettings.eeecurrency_transaction_code.value;
  document.testsettings.submit();
}

function test_pecunix() {';
  if ($settings['demomode'] == 1) {
      echo '  alert("Sorry, not available in demo mode");
  return false;';
  }

  if (!function_exists('curl_init')) {
      echo '  alert("Sorry, curl extension is not installed on server";
  return false;';
  }

  echo '
  if (document.formsettings.pecunix_from_account.value == \'\') {
    alert("Please type Pecunix account");
    return false;
  }
  if (document.formsettings.pecunix_password.value == \'\') {
    alert("Please type Pecunix password");
    return false;
  }

  window.open(\'\', \'testpecunix\', \'width=400, height=200, status=0\');
  document.testsettings.target = \'testpecunix\';
  document.tests';
  echo 'ettings.a.value = \'test_pecunix_settings\';
  document.testsettings.acc.value = document.formsettings.pecunix_from_account.value;
  document.testsettings.pass.value = document.formsettings.pecunix_password.value;
  document.testsettings.submit();
}
</script>

<form name=testsettings method=post>
<input type=hidden name=a>
<input type=hidden name=acc>
<input type=hidden name=pass>
<input type=h';
  echo 'idden name=code>
</form>

<form method=post name=formsettings>
<input type=hidden name=a value=auto-pay-settings>
<input type=hidden name=action value=auto-pay-settings>

<b>Auto-payment settings:</b><br><br>
  ';
  if (!function_exists('curl_init')) {
      echo '  ';
      echo start_info_table('100%');
      echo '  <b>Auto-payment is not available</b><br>
  Curl module is not installed on your server.
  ';
      echo end_info_table();
      echo '  <br>
  <br>';
  }

  echo '

<table cellspacing=0 cellpadding=2 border=0 width=100%>
<tr>
 <td colspan=2><input type=checkbox name=use_auto_payment value=1 ';
  echo $settings['use_auto_payment'] == 1 ? 'checked' : '';
  echo '> Use auto-payment</td>
</tr><tr>
 <td colspan=2><br>
 <b>E-gold account:</b></td>
</tr><tr>
 <td>Account number:</td>
 <td><input type=text name=egold_from_account value="';
  echo $settings['egold_from_account'];
  echo '" class=inpts size=30></td>
</tr>';
  if ($egold_password != '') {
      echo '<tr>
 <td>Old passphrase:</td>
 <td>**********</td>
</tr>';
  }

  echo '<tr>
 <td>Account passphrase:</td>
 <td><input type=password name=egold_account_password value="" class=inpts size=30> <input type=button value="Test" onClick="test_egold();" class=sbmt></td>
</tr>

<!--tr>
      <td colspan=2><b>Evocash account:</b></b></td>
</tr><tr>
 <td>Account number:</td>
 <td><input type=text name=evocash_from_account value="';
  echo $settings['evocash_from_account'];
  echo '" class=inpts size=30></td>
</tr><tr>
 <td>Account username:</td>
 <td><input type=text name=evocash_username value="';
  echo $settings['evocash_username'];
  echo '" class=inpts size=30></td>
</tr>';
  if ($evocash_password != '') {
      echo '<tr>
 <td>Old password:</td>
 <td>**********</td>
</tr>';
  }

  echo '<tr>
 <td>Account password:</td>
 <td><input type=password name=evocash_account_password value="" class=inpts size=30></td>
</tr>';
  if ($evocash_transaction_code != '') {
      echo '<tr>
 <td>Old transaction code:</td>
 <td>**********</td>
</tr>';
  }

  echo '<tr>
 <td>Transaction code:</td>
 <td><input type=password name=evocash_transaction_code value="" class=inpts size=30> <input type=button value="Test" onClick="test_evocash();" class=sbmt></td>
</tr-->


<tr>
 <td colspan=2><b>IntGold settings:</b></td>
</tr>
<tr>
 <td>Account Id:</td>
 <td><input type=text name=intgold_from_account value="';
  echo $settings['intgold_from_account'];
  echo '" class=inpts size=30></td>
</tr>';
  if ($intgold_password != '') {
      echo '<tr>
 <td>Old password:</td>
 <td>**********</td>
</tr>';
  }

  echo '
<tr>
 <td>Password:</td>
 <td><input type=password name=intgold_password value="" class=inpts size=30></td>
</tr>';
  if ($intgold_transaction_code != '') {
      echo '<tr>
 <td>Old secondary password:</td>
 <td>**********</td>
</tr>';
  }

  echo '<tr>
 <td>Secondary password:</td>
 <td><input type=password name=intgold_transaction_code value="" class=inpts size=30> <input type=button value="Test" onClick="test_intgold();" class=sbmt></td>
</tr>

<tr>
 <td colspan=2><b>eeeCurrency settings:</b></td>
</tr>
<tr>
 <td>Account Id:</td>
 <td><input type=text name=eeecurrency_from_account value="';
  echo $settings['eeecurrency_from_account'];
  echo '" class=inpts size=30></td>
</tr>';
  if ($eeecurrency_password != '') {
      echo '<tr>
 <td>Old password:</td>
 <td>**********</td>
</tr>';
  }

  echo '
<tr>
 <td>Password:</td>
 <td><input type=password name=eeecurrency_password value="" class=inpts size=30></td>
</tr>';
  if ($eeecurrency_transaction_code != '') {
      echo '<tr>
 <td>Old secondary password:</td>
 <td>**********</td>
</tr>';
  }

  echo '<tr>
 <td>Secondary password:</td>
 <td><input type=password name=eeecurrency_transaction_code value="" class=inpts size=30> <input type=button value="Test" onClick="test_eeecurrency();" class=sbmt></td>
</tr>

<tr>
 <td colspan=2><b>Pecunix settings:</b></td>
</tr>
<tr>
 <td>Account Id:</td>
 <td><input type=text name=pecunix_from_account value="';
  echo $settings['pecunix_from_account'];
  echo '" class=inpts size=30></td>
</tr>';
  if ($pecunix_password != '') {
      echo '<tr>
 <td>Old Payment PIK:</td>
 <td>**********</td>
</tr>';
  }

  echo '<tr>
 <td>Payment PIK:<br>';
  echo '<s';
  echo 'mall>You should enter just characters without spaces and \'-\'</small></td>
 <td><input type=password name=pecunix_password value="" class=inpts size=30> <input type=button value="Test" onClick="test_pecunix();" class=sbmt></td>
</tr>

<tr>
 <td colspan=2><b>Other settings:</b></td>
</tr>
<tr>
      <td>Minimal automatic withdrawal amount (US$):</td>
 <td><input type=text name=min_auto_withdraw value="';
  echo $settings['min_auto_withdraw'];
  echo '" class=inpts size=30></td>
</tr><tr>
      <td>Maximal automatic withdrawal amount (US$):</td>
 <td><input type=text name=max_auto_withdraw value="';
  echo $settings['max_auto_withdraw'];
  echo '" class=inpts size=30></td>
</tr><tr>
      <td>Maximal daily withdrawal for every user. (US$):</td>
 <td><input type=text name=max_auto_withdraw_user value="';
  echo $settings['max_auto_withdraw_user'];
  echo '" class=inpts size=30></td>
</tr>';
  if ($userinfo['transaction_code'] != '') {
      echo '<tr>
 <td colspan=2>&nbsp;</td>
</tr>
<tr>
 <td>Alternative Passphrase: </td>
 <td><input type=password name="alternative_passphrase" value="" class=inpts size=30></td>
</tr>';
  }

  echo '<tr>
 <td>&nbsp;</td>
 <td><input type=submit value="Save" class=sbmt></td>
</tr>
</table>
<br>

</form>
';
  $q = 'select * from hm2_pay_errors limit 1';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      echo '<a href=?a=error_pay_log>Check error transactions</a>
<br><br>
';
  }

  echo start_info_table('100%');
  echo '<b>Payer account information</b><br>
Type your login and password here. <br>
For e-gold: Make sure you\'ve entered your server IP at "Account Info" -> "Account
Attributes" -> "Automation Access".<br>
<!--For Evocash: Be sure you enter your server IP at "Information of account"-->.<br>
The Password will be encrypted and saved to the mysql database.<br>
<br>
Minimal automatic withdrawal amount and<br>';
  echo '
Maximal automatic withdrawal amount.<br>
Withdrawal will be processed automatically if a user asks to withdraw more or
equal than the minimal withdrawal amount and less or equal than the maximual withdrawal
amount. Administrator should process all other transactions manually.<br>
Maximal daily withdrawal for every user. The script will make payments to the
user\'s e-gold account automaticall';
  echo 'y if the total user withdrawal sum for 24 hour
is less than the specified value.<br>
<br>

E-gold:<br>
Test button tries to spend $0.01 from and to your account number. It returns error
if your settings are wrong.<br><br>

IntGold:<br>
Test button tries to spend $0.01 from and to your account number in test mode. It returns error if your settings
are incorrect.<br><br>


eeeCurrency:<br>
Te';
  echo 'st button tries to spend $0.01 from and to your account number in test mode. It returns error if your settings
are incorrect.<br><br>

Pecunix:<br>
Test button tries to spend $0.01 from and to your account number in test mode. It returns error if your settings
are incorrect.<br><br>

<!--Evocash:<br>
Test button tries to spend $0.01 from and to your account number, but no transactions
will be m';
  echo 'ade, because Evocash does not allow spends to the same account. The error
is returned if your settings are wrong. -->';
  echo end_info_table();
