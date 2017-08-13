<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$q = 'select * from hm2_types order by id';
  if (!($sth = db_query($q))) {
  }

  $plans = [];
  $periods = ['d' => 'daily', 'w' => 'weekly', 'b-w' => 'bi-weekly', 'm' => 'monthly', '2m' => 'every 2 month', '3m' => 'every 3 month', '6m' => 'every 6 month', 'y' => 'yearly'];
  while ($row = mysql_fetch_array($sth)) {
      $q = 'select min(min_deposit) as min_amount, max(max_deposit) as max_amount, sum(max_deposit = 0) as nomax, min(percent) as min_percent, max(percent) as max_percent from hm2_plans where parent='.$row['id'].' group by parent';
      if (!($sth1 = db_query($q))) {
      }

      $row1 = mysql_fetch_array($sth1);
      if ($row1['nomax'] == 0) {
          $row['deposit'] = '$'.$row1['min_amount'].(''.' - $').$row1['max_amount'];
      } else {
          $row['deposit'] = 'from $'.$row1['min_amount'];
      }

      $percent = $row1['min_percent'];
      if ($percent < $row1['max_percent']) {
          $percent .= ' - '.$row1['max_percent'];
      }

      if ($row['period'] != 'end') {
          $row['percent'] = $percent.'% / '.$periods[$row['period']];
      } else {
          $row['percent'] = $percent.'%';
      }

      $q = 'select * from hm2_plans where parent='.$row['id'].' order by id';
      $sth1 = db_query($q);
      $row['plans'] = [];
      while ($row1 = mysql_fetch_array($sth1)) {
          if ($row1['max_deposit'] == 0) {
              $row1['max_deposit'] = 'unlimited';
          }

          array_push($row['plans'], $row1);
      }

      array_push($plans, $row);
  }

  echo '



<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr>
 <td colspan=3><b>Available Investment Packages:</b></td>
</tr><tr>
 <td bgcolor=FFEA00 align=center><b>Package name</b></td>
 <td bgcolor=FFEA00 align=center><b>Deposit (US$)</b></td>
 <td bgcolor=FFEA00 align=center><b>Profit (%)</b></td>
 <td bgcolor=FFEA00 align=center><b>-</b></td>
</tr>';
  if (0 < count($plans)) {
      foreach ($plans as $line) {
          echo '<tr>
 <td bgcolor=FFF9B3>';
          echo htmlspecialchars($line['name']);
          echo ' ';
          echo $line['status'] == 'off' ? '<small>(inactive)</small' : '';
          echo '</td>
 <td bgcolor=FFF9B3>';
          echo $line['deposit'];
          echo '</td>
 <td bgcolor=FFF9B3>';
          echo $line['percent'];
          echo '</td>
 <td bgcolor=FFF9B3 class=menutxt align=right><a href=?a=editrate&id=';
          echo $line['id'];
          echo '>[edit]</a> <a href=?a=deleterate&id=';
          echo $line['id'];
          echo ' onclick="return confirm(\'';
          echo ($line['id'] < 3 and $settings['demomode'] == 1) ? 'Demo version restriction!\\nYou cannot delete this package!\\n\\n' : '';
          echo 'Are you sure delete this package? All users deposits in this package will be lost!\');">[delete]</a></td>
</tr>
<tr>
 <td colspan=4>
<table cellspacing=0 cellpadding=2 border=0 width=66% align=right>';
          $plans_lines = $line['plans'];
          if (0 < count($plans_lines)) {
              foreach ($plans_lines as $line1) {
                  echo '<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td nowrap width=120>';
                  echo htmlspecialchars($line1['name']);
                  echo '</td>
 <td align=right nowrap>$';
                  echo $line1['min_deposit'];
                  echo ' - ';
                  echo $line1['max_deposit'];
                  echo ' &nbsp; &nbsp; </td>
 <td align=right nowrap width=60>';
                  echo $line1['percent'];
                  echo '%</td>';
              }
          }

          echo '</tr></table>
 </td>
</tr>';
      }
  } else {
      echo '<tr>
 <td bgcolor=FFF9B3 colspan=4>No HYIPs found</td>
</tr>';
  }

  echo '</table>
<br>
<form method=get>
<input type=hidden name=a value=\'add_hyip\'>
<input type=submit value="Add a new Investment Package" class=sbmt size=15>
</form>

<br>';
  echo start_info_table('100%');
  echo 'Investment packages:<br>
You can create unlimited sets of investment packages with any settings and payout options. 
Also you can change status of any package.
<li> Active package. All active users will receive earnings every pay period if 
  made a deposit</li>
<li> Inactive package. Users will not receive any earnings</li>
<br><br>
Here you can view, edit and delete your packages and plans.';
  echo end_info_table();
  echo '

';
