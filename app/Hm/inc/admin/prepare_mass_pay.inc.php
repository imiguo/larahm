<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if (!is_array($frm['pend'])) {
    echo 'Please select withdraw requests first';
    exit();
} else {
    $ids = implode(', ', array_keys($frm['pend']));
    $sum = 0;
    $q = 'select actual_amount from hm2_history where id in ('.$ids.') and ec in (0, 1, 2, 5, 8, 9)';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $amount = abs($row['actual_amount']);
        $fee = floor($amount * $settings['withdrawal_fee']) / 100;
        if ($fee < $settings['withdrawal_fee_min']) {
            $fee = $settings['withdrawal_fee_min'];
        }

        $to_withdraw = $amount - $fee;
        if ($to_withdraw < 0) {
            $to_withdraw = 0;
        }

        $to_withdraw = number_format(floor($to_withdraw * 100) / 100, 2);
        $sum += $to_withdraw;
    }

    $amount = $sum;
}

  echo ' <b>Mass Payment:</b><br>
<br>';
  if ($settings['demomode'] != 1) {
      echo '
<form method=post name=payform onsubmit="return di_sabled()">
<input type=hidden name=a value=mass>
<input type=hidden name=action2 value=masspay>
<input type=hidden name=action3 value=masspay>
  ';
  }

  echo '  ';
  $ids = $frm['pend'];
  if (is_array($ids)) {
      reset($ids);
      while (list($kk, $vv) = each($ids)) {
          echo '<input type=hidden name=pend['.$kk.'] value=1>';
      }
  }

  echo '  Are you sure you want to pay <b>$';
  echo number_format(abs($amount), 2);
  echo '</b>
  ?<br>
  <br>';
  echo '<s';
  echo 'cript language=javascript>
function di_sabled() {
  document.payform.submit_but.disabled = true;
  return true;
}
function en_it() {
  document.payform.egold_account.disabled = document.payform.e_acc[0].checked
  document.payform.egold_password.disabled = document.payform.e_acc[0].checked
/*
  document.payform.evocash_account.disabled = document.payform.evo_acc[0].checked
  document.payf';
  echo 'orm.evocash_name.disabled = document.payform.evo_acc[0].checked
  document.payform.evocash_password.disabled = document.payform.evo_acc[0].checked
  document.payform.evocash_code.disabled = document.payform.evo_acc[0].checked
*/
  document.payform.intgold_account.disabled = document.payform.intgold_acc[0].checked
  document.payform.intgold_password.disabled = document.payform.intgold_acc[0].c';
  echo 'hecked
  document.payform.intgold_code.disabled = document.payform.intgold_acc[0].checked

  document.payform.eeecurrency_account.disabled = document.payform.eeecurrency_acc[0].checked
  document.payform.eeecurrency_password.disabled = document.payform.eeecurrency_acc[0].checked
  document.payform.eeecurrency_code.disabled = document.payform.eeecurrency_acc[0].checked

  document.payform.pe';
  echo 'cunix_account.disabled = document.payform.pecunix_acc[0].checked
  document.payform.pecunix_password.disabled = document.payform.pecunix_acc[0].checked
  document.payform.pecunix_code.disabled = document.payform.pecunix_acc[0].checked

}
</script>
<b>E-gold account:</b><br>
<input type=radio name=e_acc value=0 onClick="en_it();" checked>
  Pay from the saved account (check auto-payment settings';
  echo ' screen)<br>
<input type=radio name=e_acc value=1 onClick="en_it();">
  Pay from the other account (specify below):<br>
<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td>Account number:</td>
 <td><input type=text name=egold_account value="" class=inpts size=30></td>
</tr><tr>
 <td>Passphrase:</td>
 <td><input type=password name=egold_password value="" class=inpts size=30></td>
</tr></table>
<!--br>
<b';
  echo '>Evocash account:</b><br>
<input type=radio name=evo_acc value=0 onClick="en_it();" checked>
  Pay from the saved account (check auto-payment settings screen)<br>
<input type=radio name=evo_acc value=1 onClick="en_it();">
  Pay from the other account (specify below):<br>
<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td>Account number:</td>
 <td><input type=text name=evocash_account value="" clas';
  echo 's=inpts size=30></td>
</tr><tr>
 <td>Account name:</td>
 <td><input type=text name=evocash_name value="" class=inpts size=30></td>
</tr><tr>
 <td>Password:</td>
 <td><input type=password name=evocash_password value="" class=inpts size=30></td>
</tr><tr>
 <td>Code:</td>
 <td><input type=password name=evocash_code value="" class=inpts size=30></td>
</tr></table-->


<b>IntGold account:</b><br>
<input type=radio n';
  echo 'ame=intgold_acc value=0 onClick="en_it();" checked>
  Pay from the saved account (check auto-payment settings screen)<br>
<input type=radio name=intgold_acc value=1 onClick="en_it();">
  Pay from the other account (specify below):<br>
<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td>Account number:</td>
 <td><input type=text name=intgold_account value="" class=inpts size=30></td>
</tr><tr>
 <td>';
  echo 'Password:</td>
 <td><input type=password name=intgold_password value="" class=inpts size=30></td>
</tr><tr>
 <td>Code:</td>
 <td><input type=password name=intgold_code value="" class=inpts size=30></td>
</tr></table>

<b>eeeCurrency account:</b><br>
<input type=radio name=eeecurrency_acc value=0 onClick="en_it();" checked>
  Pay from the saved account (check auto-payment settings screen)<br>
<input type=ra';
  echo 'dio name=eeecurrency_acc value=1 onClick="en_it();">
  Pay from the other account (specify below):<br>
<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td>Account number:</td>
 <td><input type=text name=eeecurrency_account value="" class=inpts size=30></td>
</tr><tr>
 <td>Password:</td>
 <td><input type=password name=eeecurrency_password value="" class=inpts size=30></td>
</tr><tr>
 <td>Code:</td>
 <td>';
  echo '<input type=password name=eeecurrency_code value="" class=inpts size=30></td>
</tr></table>


<b>Pecunix account:</b><br>
<input type=radio name=pecunix_acc value=0 onClick="en_it();" checked>
  Pay from the saved account (check auto-payment settings screen)<br>
<input type=radio name=pecunix_acc value=1 onClick="en_it();">
  Pay from the other account (specify below):<br>
<table cellspacing=0 cellpa';
  echo 'dding=2 border=0>
<tr>
 <td>Account number:</td>
 <td><input type=text name=pecunix_account value="" class=inpts size=30></td>
</tr><tr>
 <td>Password:</td>
 <td><input type=password name=pecunix_password value="" class=inpts size=30></td>
</tr><tr>
 <td>Code:</td>
 <td><input type=password name=pecunix_code value="" class=inpts size=30></td>
</tr><tr>
 <td>&nbsp;</td>
 <td><input type=submit name=submit_but va';
  echo 'lue="Pay" class=sbmt></td>
</tr></table>

</form>';
  echo '<s';
  echo 'cript language=javascript>en_it();</script>
';
  if ($settings['demomode'] == 1) {
      echo start_info_table('100%');
      echo '<b>Demo restriction!</b><br>
Not Available in demo!<br><br>

Available in Pro version only!!!';
      echo end_info_table();
  }
