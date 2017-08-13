<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$user_id = sprintf('%d', $frm['u_id']);
  $q = 'select * from hm2_types where status = \'on\'';
  $sth = db_query($q);
  $plans = [];
  $deposits_cnt = 0;
  while ($row = mysql_fetch_array($sth)) {
      $row['deposits'] = [];
      $q = 'select
                *,
                date_format(deposit_date + interval '.$settings['time_dif'].' hour, \'%b-%e-%Y %r\') as date,
                (to_days(now()) - to_days(deposit_date)) as duration,
                (to_days(now()) - to_days(deposit_date) - '.$row['withdraw_principal_duration'].(''.') as pending_duration
          from
                hm2_deposits
          where
                user_id = '.$user_id.' and
                status=\'on\' and
                type_id = ').$row['id'].'
          order by
                deposit_date
         ';
      $sth1 = db_query($q);
      $d = [];
      while ($row1 = mysql_fetch_array($sth1)) {
          array_push($d, $row1[id]);
          $row1['can_withdraw'] = 1;
          if ($row['withdraw_principal'] == 0) {
              $row1['can_withdraw'] = 0;
          } else {
              if ($row1['duration'] < $row['withdraw_principal_duration']) {
                  $row1['can_withdraw'] = 0;
              }

              if (($row['withdraw_principal_duration_max'] != 0 and $row['withdraw_principal_duration_max'] <= $row1['duration'])) {
                  $row1['can_withdraw'] = 0;
              }
          }

          $row1['deposit'] = number_format(floor($row1['actual_amount'] * 100) / 100, 2);
          $row1['compound'] = sprintf('%.02f', $row1['compound']);
          $row1['pending_duration'] = 0 - $row1['pending_duration'];
          array_push($row['deposits'], $row1);
          ++$deposits_cnt;
      }

      $q = 'select
            sum(hm2_history.actual_amount) as sm
          from
            hm2_history, hm2_deposits
          where
            hm2_history.deposit_id = hm2_deposits.id and
            hm2_history.user_id = '.$user_id.' and
            hm2_deposits.user_id = '.$user_id.' and
            hm2_history.type=\'deposit\' and
            hm2_deposits.type_id = '.$row['id'];
      $sth1 = db_query($q);
      $row1 = mysql_fetch_array($sth1);
      $row['total_deposit'] = number_format(abs($row1['sm']), 2);
      $q = 'select
            sum(hm2_history.actual_amount) as sm
          from
            hm2_history, hm2_deposits
          where
            hm2_history.deposit_id = hm2_deposits.id and
            hm2_history.user_id = '.$user_id.' and
            hm2_deposits.user_id = '.$user_id.' and
            hm2_history.type=\'earning\' and
            to_days(hm2_history.date + interval '.$settings['time_dif'].' hour) = to_days(now()) and
            hm2_deposits.type_id = '.$row['id'];
      $sth1 = db_query($q);
      $row1 = mysql_fetch_array($sth1);
      $row['today_profit'] = number_format(abs($row1['sm']), 2);
      $q = 'select
            sum(hm2_history.actual_amount) as sm
          from
            hm2_history, hm2_deposits
          where
            hm2_history.deposit_id = hm2_deposits.id and
            hm2_history.user_id = '.$user_id.' and
            hm2_deposits.user_id = '.$user_id.' and
            hm2_history.type=\'earning\' and
            hm2_deposits.type_id = '.$row['id'];
      $sth1 = db_query($q);
      $row1 = mysql_fetch_array($sth1);
      $row['total_profit'] = number_format(abs($row1['sm']), 2);
      if ((!$row['deposits'] and $row['closed'] != 0)) {
          continue;
      }

      array_push($plans, $row);
  }

  if ($settings['demomode'] == 1) {
      echo start_info_table('100%');
      echo '<b>Demo version restriction!</b><br>
You cannot release deposits!';
      echo end_info_table();
  }

  echo '


<form method=post>
<input type=hidden name=a value=releasedeposits>
<input type=hidden name=action value=releasedeposits>
<input type=hidden name=u_id value="';
  echo $user_id;
  echo '">

<b>Release Active Deposits:</b><br><br>
';
  for ($i = 0; $i < count($plans); ++$i) {
      echo '<b>';
      echo $plans[$i]['name'];
      echo '</b>
<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr>
 <td bgcolor=FFEA00 align=center width=200>Date</td>
 <td bgcolor=FFEA00 align=center>Deposit</td>
 <td bgcolor=FFEA00 align=center>Compound</td>
 <td bgcolor=FFEA00 align=center>Release</td>
 <td bgcolor=FFEA00 align=center>Released</td>
 <td bgcolor=FFEA00 align=center>Release Amount</td>
 <td bgcolor=FFEA00 align=center>Change Plan</td>';
      echo '
</tr>
';
      if (0 < count($plans[$i][deposits])) {
          for ($j = 0; $j < count($plans[$i][deposits]); ++$j) {
              echo '<tr>
 <td nowrap>';
              echo $plans[$i][deposits][$j]['date'];
              echo '</td>
 <td align=right>$';
              echo number_format($plans[$i][deposits][$j]['actual_amount'], 2);
              echo '</td>
 <td align=right>';
              echo $plans[$i][deposits][$j][compound];
              echo '%</td>
 <td align=center nowrap>
  ';
              if ($plans[$i][deposits][$j][can_withdraw]) {
                  echo '   Can Release
  ';
              } else {
                  echo '   ';
                  if (0 < $plans[$i][deposits][$j][pending_duration]) {
                      echo '    ';
                      echo $plans[$i][deposits][$j][pending_duration];
                      echo ' days left
   ';
                  } else {
                      echo '    Not Available Any More
   ';
                  }

                  echo '  ';
              }

              echo ' </td>
 <td align=right>$';
              echo number_format($plans[$i][deposits][$j]['amount'] - $plans[$i][deposits][$j]['actual_amount'], 2);
              echo '</td>
 <td align=right><input type=text name="release[';
              echo $plans[$i][deposits][$j]['id'];
              echo ']" value="0.00" class=inpts size=20 style="text-align: right"></td>
 <td>
  ';
              echo '<s';
              echo 'elect name="type_id[';
              echo $plans[$i][deposits][$j]['id'];
              echo ']" class=inpts>';
              reset($plans);
              foreach ($plans as $plan) {
                  echo '   <option value="';
                  echo $plan['id'];
                  echo '" ';
                  echo $plan['id'] == $plans[$i][deposits][$j]['type_id'] ? 'selected' : '';
                  echo '>';
                  echo $plan['name'];
                  echo '</option>';
              }

              echo '  </select>
 </td>
</tr>';
          }
      } else {
          echo '<tr><td colspan=6 align=center>No deposits found</td></tr>';
      }

      if (((0 < $plans[$i][total_deposit] or 0 < $plans[$i][today_profit]) or 0 < $plans[$i][total_profit])) {
          echo '<tr>
 <td colspan=6>
<table cellspacing=0 cellpadding=1 border=0>
<tr><td>Total Deposited:</td><td><b>$';
          echo $plans[$i][total_deposit];
          echo '</b></td></tr>
<tr><td>Profit Today:</td><td><b>$';
          echo $plans[$i][today_profit];
          echo '</b></td></tr>
<tr><td>Profit for All Time:</td><td><b>$';
          echo $plans[$i][total_profit];
          echo '</b></td></tr>
</table>
 </td>
</tr>';
      }

      echo '</table><br>';
  }

  if (0 < $deposits_cnt) {
      echo '<input type=submit value="Save Changes" class=sbmt>';
  }

  echo '</form>
<br>
';
  echo start_info_table('100%');
  echo 'Release deposits:<br>
A member can ask you to clear his deposit and return his funds.<br>
This screen helps you to release user\'s deposit if you need. Funds will return
to the member\'s account and the member can withdraw these funds.
';
  echo end_info_table();
