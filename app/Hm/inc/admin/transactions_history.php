<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$frm['day_to'] = sprintf('%d', $frm['day_to']);
  $frm['month_to'] = sprintf('%d', $frm['month_to']);
  $frm['year_to'] = sprintf('%d', $frm['year_to']);
  $frm['day_from'] = sprintf('%d', $frm['day_from']);
  $frm['month_from'] = sprintf('%d', $frm['month_from']);
  $frm['year_from'] = sprintf('%d', $frm['year_from']);
  if ($frm['day_to'] == 0) {
      $frm['day_to'] = date('j', time() + $settings['time_dif'] * 60 * 60);
      $frm['month_to'] = date('n', time() + $settings['time_dif'] * 60 * 60);
      $frm['year_to'] = date('Y', time() + $settings['time_dif'] * 60 * 60);
      $frm['day_from'] = 1;
      $frm['month_from'] = $frm['month_to'];
      $frm['year_from'] = $frm['year_to'];
  }

  $datewhere = '\''.$frm['year_from'].'-'.$frm['month_from'].'-'.$frm['day_from'].'\' + interval 0 day < date + interval '.$settings['time_dif'].' hour and '.'\''.$frm['year_to'].'-'.$frm['month_to'].'-'.$frm['day_to'].'\' + interval 1 day > date + interval '.$settings['time_dif'].' hour ';
  if ($frm['ttype'] != '') {
      if ($frm['ttype'] == 'exchange') {
          $typewhere = ' and (type=\'exchange_out\' or type=\'exchange_in\')';
      } else {
          $typewhere = ' and type=\''.quote($frm['ttype']).'\' ';
      }
  }

  $u_id = sprintf('%d', $frm['u_id']);
  if (1 < $u_id) {
      $userwhere = ' and user_id = '.$u_id.' ';
  }

  $ecwhere = '';
  if ($frm[ec] == '') {
      $frm[ec] = -1;
  }

  $ec = sprintf('%d', $frm[ec]);
  if (-1 < $frm[ec]) {
      $ecwhere = ' and ec = '.$ec;
  }

  $q = 'select count(*) as col from hm2_history where '.$datewhere.' '.$userwhere.' '.$typewhere.' '.$ecwhere;
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $count_all = $row['col'];
  $page = $frm['page'];
  $onpage = 20;
  $colpages = ceil($count_all / $onpage);
  if ($page <= 1) {
      $page = 1;
  }

  if (($colpages < $page and 1 <= $colpages)) {
      $page = $colpages;
  }

  $from = ($page - 1) * $onpage;
  $order = ($settings['use_history_balance_mode'] ? 'asc' : 'desc');
  $dformat = ($settings['use_history_balance_mode'] ? '%b-%e-%Y<br>%r' : '%b-%e-%Y %r');
  $q = 'select *, date_format(date + interval '.$settings['time_dif'].(''.' hour, \''.$dformat.'\') as d from hm2_history where '.$datewhere.' '.$userwhere.' '.$typewhere.' '.$ecwhere.' order by date '.$order.', id '.$order.' limit '.$from.', '.$onpage);
  $sth = db_query($q);
  $trans = [];
  while ($row = mysql_fetch_array($sth)) {
      $q = 'select username from hm2_users where id = '.$row['user_id'];
      $sth1 = db_query($q);
      $row1 = mysql_fetch_array($sth1);
      if ($row1) {
          $row['username'] = $row1['username'];
      } else {
          $row['username'] = '-- deleted user --';
      }

      $row['debitcredit'] = ($row['actual_amount'] < 0 ? 1 : 0);
      if ($settings['use_history_balance_mode']) {
          $q = 'select sum(actual_amount) as balance from hm2_history where id < '.$row['id'].(''.' '.$userwhere);
          $sth1 = db_query($q);
          $row1 = mysql_fetch_array($sth1);
          $start_balance = $row1['balance'];
          $row['balance'] = number_format($start_balance + $row['actual_amount'], 2);
      }

      array_push($trans, $row);
  }

  if ($settings['use_history_balance_mode']) {
      $q = 'select
            sum(actual_amount * (actual_amount < 0)) as debit,
            sum(actual_amount * (actual_amount > 0)) as credit,
            sum(actual_amount) as balance
          from
            hm2_history where '.$datewhere.' '.$typewhere.' '.$userwhere.' '.$ecwhere;
      $sth = db_query($q);
      $period_stats = mysql_fetch_array($sth);
      $q = 'select
            sum(actual_amount * (actual_amount < 0)) as debit,
            sum(actual_amount * (actual_amount > 0)) as credit,
            sum(actual_amount) as balance
          from
            hm2_history where 1=1 '.$typewhere.' '.$userwhere.' '.$ecwhere;
      $sth = db_query($q);
      $total_stats = mysql_fetch_array($sth);
  }

  $month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  $q = 'select sum(actual_amount) as periodsum from hm2_history where '.$datewhere.' '.$userwhere.' '.$typewhere.' '.$ecwhere;
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $periodsum = $row['periodsum'];
  $q = 'select sum(actual_amount) as sum from hm2_history where 1=1 '.$userwhere.' '.$typewhere.' '.$ecwhere;
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $allsum = $row['sum'];
  echo '<s';
  echo 'cript language=javascript>
function go(p)
{
  document.trans.page.value = p;
  document.trans.submit();
}
</script>

<form method=post name=trans>
<input type=hidden name=a value=thistory>
<input type=hidden name=action2>
<input type=hidden name=u_id value=\'';
  echo $frm['u_id'];
  echo '\'>
<input type=hidden name=page value=\'';
  echo $page;
  echo '\'>
<table cellspacing=0 cellpadding=0 border=0 width=100%>
<tr>
 <td>
	<b>Transactions history:</b>
	<br><img src=images/q.gif width=1 height=4><br>
	';
  echo '<s';
  echo 'elect name=ttype class=inpts onchange="document.trans.action2.value=\'\';document.trans.submit()">
		<option value="">All transactions
		<option value="add_funds" ';
  echo $frm['ttype'] == 'add_funds' ? 'selected' : '';
  echo '>Transfers from external processings
		<option value="deposit" ';
  echo $frm['ttype'] == 'deposit' ? 'selected' : '';
  echo '>Deposits
		<option value="bonus" ';
  echo $frm['ttype'] == 'bonus' ? 'selected' : '';
  echo '>Bonuses
		<option value="penality" ';
  echo $frm['ttype'] == 'penality' ? 'selected' : '';
  echo '>Penalties
		<option value="earning" ';
  echo $frm['ttype'] == 'earning' ? 'selected' : '';
  echo '>Earnings
		<option value="withdrawal" ';
  echo $frm['ttype'] == 'withdrawal' ? 'selected' : '';
  echo '>Withdrawals
		<option value="withdraw_pending" ';
  echo $frm['ttype'] == 'withdraw_pending' ? 'selected' : '';
  echo '>Withdrawal requests
	  <option value="commissions" ';
  echo $frm['ttype'] == 'commissions' ? 'selected' : '';
  echo '>Commissions
    <option value="early_deposit_release" ';
  echo $frm['ttype'] == 'early_deposit_release' ? 'selected' : '';
  echo '>Early deposit releases
<!--		<option value="early_deposit_charge" ';
  echo $frm['ttype'] == 'early_deposit_charge' ? 'selected' : '';
  echo '>Deposit releases commisions-->
		<option value="release_deposit" ';
  echo $frm['ttype'] == 'release_deposit' ? 'selected' : '';
  echo '>Deposit returns
		<option value="exchange" ';
  echo $frm['ttype'] == 'exchange' ? 'selected' : '';
  echo '>Exchange
	</select>
<br>
	';
  echo '<s';
  echo 'elect name=ec class=inpts>
	  <option value=-1>All eCurrencies</option>';
  foreach ($exchange_systems as $id => $data) {
      if ($data[status] == 1) {
          echo '<option value=';
          echo $id;
          echo ' ';
          echo $id == $frm[ec] ? 'selected' : '';
          echo '>';
          echo $data[name];
          echo '</option>';
          continue;
      }
  }

  echo '	</select>

 </td>
 <td align=right>
	From: ';
  echo '<s';
  echo 'elect name=month_from class=inpts>';
  for ($i = 0; $i < count($month); ++$i) {
      echo '<option value=';
      echo $i + 1;
      echo ' ';
      echo $i + 1 == $frm['month_from'] ? 'selected' : '';
      echo '>';
      echo $month[$i];
  }

  echo '        </select> &nbsp;
	';
  echo '<s';
  echo 'elect name=day_from class=inpts>';
  for ($i = 1; $i <= 31; ++$i) {
      echo '<option value=';
      echo $i;
      echo ' ';
      echo $i == $frm['day_from'] ? 'selected' : '';
      echo '>';
      echo $i;
  }

  echo '	</select> &nbsp;
	';
  echo '<s';
  echo 'elect name=year_from class=inpts>';
  for ($i = $settings['site_start_year']; $i <= date('Y', time() + $settings['time_dif'] * 60 * 60); ++$i) {
      echo '<option value=';
      echo $i;
      echo ' ';
      echo $i == $frm['year_from'] ? 'selected' : '';
      echo '>';
      echo $i;
  }

  echo '	</select><br><img src=images/q.gif width=1 height=4><br>



	To: ';
  echo '<s';
  echo 'elect name=month_to class=inpts>';
  for ($i = 0; $i < count($month); ++$i) {
      echo '<option value=';
      echo $i + 1;
      echo ' ';
      echo $i + 1 == $frm['month_to'] ? 'selected' : '';
      echo '>';
      echo $month[$i];
  }

  echo '        </select> &nbsp;
	';
  echo '<s';
  echo 'elect name=day_to class=inpts>';
  for ($i = 1; $i <= 31; ++$i) {
      echo '<option value=';
      echo $i;
      echo ' ';
      echo $i == $frm['day_to'] ? 'selected' : '';
      echo '>';
      echo $i;
  }

  echo '	</select> &nbsp;
	';
  echo '<s';
  echo 'elect name=year_to class=inpts>';
  for ($i = $settings['site_start_year']; $i <= date('Y', time() + $settings['time_dif'] * 60 * 60); ++$i) {
      echo '<option value=';
      echo $i;
      echo ' ';
      echo $i == $frm['year_to'] ? 'selected' : '';
      echo '>';
      echo $i;
  }

  echo '	</select>
 </td>
 <td>
	&nbsp; <input type=submit value="Go" class=sbmt>
<br>';
  echo '<s';
  echo 'cript language=javascript>
function func5() {
  document.trans.action2.value=\'download_csv\';
  document.trans.submit();
}
</script>
	&nbsp; <input type=button value="Download CSV" class=sbmt onClick="func5();">
 </td>
</tr></table>
</form>

<br><br>
<form method=post target=_blank name=massform>
<input type=hidden name=a value=mass>
<input type=hidden name=action value=mass>
<input type=hidden name=action2 va';
  echo 'lue=\'\'>
';
  if (($frm['ttype'] == 'withdraw_pending' and $frm['say'] == 'yes')) {
      echo 'Withdrawal has been sent<br><br>';
  }

  if (($frm['ttype'] == 'withdraw_pending' and $frm['say'] == 'no')) {
      echo 'Withdrawal has not been sent<br><br>';
  }

  if ($frm['say'] == 'massremove') {
      echo 'Pending transactions removed!<br><br>';
  }

  if ($frm['say'] == 'massprocessed') {
      echo 'Pending transactions selected as processed!<br><br>';
  }

  if ($settings['use_history_balance_mode']) {
      echo '
<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr>
 <td bgcolor=FFEA00 align=center><b>UserName</b></td>
 <td bgcolor=FFEA00 align=center><b>Date</b></td>
 <td bgcolor=FFEA00 align=center><b>Description</b></td>
 <td bgcolor=FFEA00 align=center><b>Credit</b></td>
 <td bgcolor=FFEA00 align=center><b>Debit</b></td>
 <td bgcolor=FFEA00 align=center><b>Balance</b></td>
 <td bgcolor=FFEA00 align=center><b>P.S.</b></td>
</';
      echo 'tr>';
      if (0 < count($trans)) {
          for ($i = 0; $i < count($trans); ++$i) {
              $amount = abs($trans[$i]['actual_amount']);
              $fee = floor($amount * $settings['withdrawal_fee']) / 100;
              if ($fee < $settings['withdrawal_fee_min']) {
                  $fee = $settings['withdrawal_fee_min'];
              }

              $to_withdraw = $amount - $fee;
              if ($to_withdraw < 0) {
                  $to_withdraw = 0;
              }

              $to_withdraw = number_format(floor($to_withdraw * 100) / 100, 2);
              echo '<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>';
              echo $frm['ttype'] == 'withdraw_pending' ? '<input type=checkbox name=pend['.$trans[$i]['id'].'] value=1> &nbsp; ' : '';
              echo '<b>';
              echo $trans[$i]['username'];
              echo '</b></td>
 <td align=center nowrap><b>';
              echo '<s';
              echo 'mall>';
              echo $trans[$i]['d'];
              echo '</small></b></td>
 <td><b>';
              echo $transtype[$trans[$i]['type']];
              echo '</b><br>';
              echo '<s';
              echo 'mall style="color: gray">';
              echo $trans[$i]['description'];
              echo '</small></td>
 <td align=right><b>
  ';
              if ($trans[$i][debitcredit] == 0) {
                  echo '  $';
                  echo number_format(abs($trans[$i][actual_amount]), 2);
                  echo '  </b>
  ';
              } else {
                  echo '  &nbsp;
  ';
              }

              echo ' </td>
 <td align=right><b>
  ';
              if ($trans[$i][debitcredit] == 1) {
                  echo '  $';
                  echo number_format(abs($trans[$i][actual_amount]), 2);
                  echo $trans[$i]['type'] == 'withdraw_pending' ? '($'.$to_withdraw.' with fees)' : '';
                  echo ' ';
                  echo $frm['ttype'] == 'withdraw_pending' ? ' &nbsp; <a href=?a=pay_withdraw&id='.$trans[$i]['id'].' target=_blank>[pay]</a> <a href=?a=rm_withdraw&id='.$trans[$i]['id'].' onClick="return confirm(\'Do you really want to remove this transaction?\')">[remove]</a>' : '';
                  echo '  </b>
  ';
              } else {
                  echo '  &nbsp;
  ';
              }

              echo ' </td>
 <td align=right><b>
  $';
              echo $trans[$i][balance];
              echo ' </td>
 <td align=center><img src="images/';
              echo $trans[$i]['ec'];
              echo '.gif" align=absmiddle hspace=1 height=17></td>
</tr>';
          }

          echo '<tr>
      <td colspan=3>For this period:</td>
 <td align=right nowrap><b>$';
          echo number_format(abs($period_stats[credit]), 2);
          echo '</b></td>
 <td align=right nowrap><b>$';
          echo number_format(abs($period_stats[debit]), 2);
          echo '</b></td>
 <td align=right nowrap><b>$';
          echo number_format($period_stats[balance], 2);
          echo '</b></td>
</tr>';
      } else {
          echo '<tr>
 <td colspan=7 align=center>No transactions found</td>
</tr>';
      }

      echo '<tr>
 <td colspan=3>Total:</td>
 <td align=right nowrap><b>$';
      echo number_format(abs($total_stats[credit]), 2);
      echo '</b></td>
 <td align=right nowrap><b>$';
      echo number_format(abs($total_stats[debit]), 2);
      echo '</b></td>
 <td align=right nowrap><b>$';
      echo number_format($total_stats[balance], 2);
      echo '</b></td>
</tr>
</table>
';
  } else {
      echo '<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr>
 <td bgcolor=FFEA00 align=center><b>UserName</b></td>
 <td bgcolor=FFEA00 align=center width=200><b>Amount</b></td>
 <td bgcolor=FFEA00 align=center width=170><b>Date</b></td>
</tr>';
      if (0 < count($trans)) {
          for ($i = 0; $i < count($trans); ++$i) {
              $amount = abs($trans[$i]['actual_amount']);
              $fee = floor($amount * $settings['withdrawal_fee']) / 100;
              if ($fee < $settings['withdrawal_fee_min']) {
                  $fee = $settings['withdrawal_fee_min'];
              }

              $to_withdraw = $amount - $fee;
              if ($to_withdraw < 0) {
                  $to_withdraw = 0;
              }

              $to_withdraw = number_format(floor($to_withdraw * 100) / 100, 2);
              echo '<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>';
              echo $frm['ttype'] == 'withdraw_pending' ? '<input type=checkbox name=pend['.$trans[$i]['id'].'] value=1> &nbsp; ' : '';
              echo '<b>';
              echo $trans[$i]['username'];
              echo '</b></td>
 <td width=200 align=right><b>$';
              echo number_format(abs($trans[$i]['actual_amount']), 2);
              echo $trans[$i]['type'] == 'withdraw_pending' ? ' ($'.$to_withdraw.' with fees)<br>' : '';
              echo '</b>';
              echo $frm['ttype'] == 'withdraw_pending' ? ' &nbsp; <a href=?a=pay_withdraw&id='.$trans[$i]['id'].' target=_blank>[pay]</a> <a href=?a=rm_withdraw&id='.$trans[$i]['id'].' onClick="return confirm(\'Really need delete this transaction?\')">[remove]</a>' : '';
              echo '<img src="images/';
              echo $trans[$i]['ec'];
              echo '.gif" align=absmiddle hspace=1 height=17></td>
 <td width=170 align=center valign=bottom><b>';
              echo '<s';
              echo 'mall>';
              echo $trans[$i]['d'];
              echo '</small></b></td>
</tr>
<tr>
 <td colspan=3 style="color: gray">';
              echo '<s';
              echo 'mall><b>';
              echo $transtype[$trans[$i]['type']];
              echo ': &nbsp; </b>';
              echo $trans[$i]['description'];
              echo '</small></td>
</tr>';
          }

          echo '<tr>
      <td colspan=2><b>For this period:</b></td>
 <td align=right><b>$ ';
          echo number_format(((($frm['ttype'] == 'deposit' or $frm['ttype'] == 'withdraw_pending') or $frm['ttype'] == 'exchange') ? '-1' : '1') * $periodsum, 2);
          echo '</b></td>
</tr>';
      } else {
          echo '<tr>
 <td colspan=3 align=center>No transactions found</td>
</tr>';
      }

      echo '<tr>
 <td colspan=2><b>Total:</b></td>
 <td align=right><b>$ ';
      echo number_format(((($frm['ttype'] == 'deposit' or $frm['ttype'] == 'withdraw_pending') or $frm['ttype'] == 'exchange') ? '-1' : '1') * $allsum, 2);
      echo '</b></td>
</tr>
</table>';
  }

  if ($frm['ttype'] == 'withdraw_pending') {
      echo '<br><center>';
      echo '<s';
      echo 'cript language=javascript>
function func1() {
  document.massform.action2.value=\'masspay\';
  if (confirm(\'Do you really want to process this withdrawal(s)?\')) {
    document.massform.submit();
  }
}
function func2() {
  document.massform.action2.value=\'massremove\';
  if (confirm("Are you sure you want to remove this withdrawal(s)?\\n\\nFunds will be returned to the user system account(s).")) {
    d';
      echo 'ocument.massform.submit();
  }
}
function func3() {
  document.massform.action2.value=\'masssetprocessed\';
  if (confirm("Are you sure you want to set this request(s) as processed?\\n\\nNo funds will be sent to the user e-gold account(s)!")) {
    document.massform.submit();
  }
}
function func4() {
  document.massform.action2.value=\'masscsv\';
  document.massform.submit();
}
</script>
<input type=butto';
      echo 'n value="Mass payment selected.';
      if ($settings['demomode'] == 1) {
          echo ' (Pro version only!)';
      }

      echo '" class=sbmt onClick="func1();"> &nbsp;
<input type=button value="Remove selected" class=sbmt onClick="func2();"> &nbsp;
<input type=button value="Set selected as processed" class=sbmt onClick="func3();"><br><br>
<input type=button value="Export selected to CSV" class=sbmt onClick="func4();">
</center><br>';
  }

  echo '</form>
<center>';
  if (1 < $colpages) {
      for ($i = 1; $i <= $colpages; ++$i) {
          if ($i == $page) {
              echo '   ';
              echo $i;
              continue;
          } else {
              echo '   <a href="javascript:go(\'';
              echo $i;
              echo '\')">';
              echo $i;
              echo '</a>';
              continue;
          }
      }
  }

  echo '
</center>';
  echo start_info_table('100%');
  echo 'Transactions history:<br>
Every transaction in the script has it\'s own type.<br>
Transfer from e-gold. This transaction will appear in the system when a user deposits
funds from e-gold.<br>
Deposit. This transaction will appear in the system when a user deposits funds
from e-gold or account.<br>
Bonus. This transaction will appear when administrator adds a bonus to a user.<br>
Penalty. This transacti';
  echo 'on will appear when administrator makes a penalty for a
user.<br>
Earning. This transaction will appear when a user receives earning.<br>
Withdrawal. This transaction will appear when administrator withdraws funds to a
user\'s e-gold account.<br>
Withdrawal request. This transaction will appear when a user asks for withdrawal.<br>
Early deposit release. Administrator can release a deposit or a part o';
  echo 'f a deposit
to a user\'s account.<br>
Referral commissions. This transaction will appear when a user registers from
a referral link and deposits funds from the e-gold account.<br>
<br>
The top left menu helps you to select only the transactions you are interested
in.<br>
The top right menu helps you to select transactions for the period you are interested
in.<br>';
  echo end_info_table();
  echo '
<br>';
  if ($frm['ttype'] == 'withdraw_pending') {
      echo start_info_table('100%');
      echo '
<br>

\'Mass payment selected\' - this button allows mass payment from any of your e-gold
accounts.<br>
\'Remove selected\' - this button allows you to remove the requested withdrawals.
Funds will be returned to the user\'s account.<br>
\'Set selected as processed\' - if you use a third party mass payment script, you
can pay to the user\'s e-gold account and then set the request as \'processed\' using
thi';
      echo 's button.<br>
\'Export selected to CSV\' - provide the scv file for a third party mass payment
scripts.<br>
';
      echo end_info_table();
  }
