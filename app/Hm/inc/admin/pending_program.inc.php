<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

echo '<html>
<head>
<link href="images/adminstyle.css" rel="stylesheet" type="text/css">
</head>
<body>';
  if ($frm['ttype'] != '') {
      $typewhere = ' and type=\'withdraw_pending\' ';
  }

  $q = 'select count(*) as col from hm2_history where type=\'withdraw_pending\'';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $count_all = $row['col'];
  $page = $frm['page'];
  $onpage = 20;
  $colpages = ceil($count_all / $onpage);
  if ($page <= 1) {
      $page = 1;
  }

  if (($colpages < $page and 1 < $colpages)) {
      $page = $colpages;
  }

  $from = ($page - 1) * $onpage;
  $q = 'select *, date_format(date, \'%b-%e-%Y %r\') as d from hm2_history where type=\'withdraw_pending\' order by date desc, id desc limit '.$from.', '.$onpage;
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

      array_push($trans, $row);
  }

  $month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  $q = 'select sum(actual_amount) as periodsum from hm2_history where type=\'withdraw_pending\'';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $periodsum = $row['periodsum'];
  $q = 'select sum(actual_amount) as sum from hm2_history where type=\'withdraw_pending\'';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $allsum = $row['sum'];
  echo '
<b>Pending Withdrawals</b><br><br>

<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr>
 <td bgcolor=FFEA00 align=center><b>UserName</b></td>
 <td bgcolor=FFEA00 align=center width=200><b>Amount</b></td>
 <td bgcolor=FFEA00 align=center width=170><b>Date</b></td>
</tr>';
  if (0 < count($trans)) {
      for ($i = 0; $i < count($trans); ++$i) {
          echo '<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td><b>';
          echo $trans[$i]['username'];
          echo '</b></td>
 <td width=200 align=right><b>$';
          echo number_format(abs($trans[$i]['actual_amount']), 2);
          echo '</b></td>
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
      echo number_format((($frm['ttype'] == 'deposit' or $frm['ttype'] == 'withdraw_pending') ? '-1' : '1') * $periodsum, 2);
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
  echo number_format((($frm['ttype'] == 'deposit' or $frm['ttype'] == 'withdraw_pending') ? '-1' : '1') * $allsum, 2);
  echo '</b></td>
</tr>
</table>
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

  echo '</center>


</body>';
