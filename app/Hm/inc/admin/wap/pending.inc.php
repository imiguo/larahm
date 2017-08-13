<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

header('Content-type: text/vnd.wap.wml');
  echo '<?xml version="1.0"?>';
  echo '<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">

<wml>
<card title="Pending withdraw">
<p>';
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
  $q = 'select *, date_format(date + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as d from hm2_history where type=\'withdraw_pending\' order by date desc, id desc limit '.$from.', '.$onpage);
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
<b>Pending Withdrawal</b><br/><br/>
';
  if (0 < count($trans)) {
      for ($i = 0; $i < count($trans); ++$i) {
          echo '<b>';
          echo $trans[$i]['username'];
          echo '</b> &nbsp; $';
          echo number_format(abs($trans[$i]['actual_amount']), 2);
          echo '&nbsp; ';
          echo '<s';
          echo 'mall>';
          echo $trans[$i]['d'];
          echo '</small><br/>';
          echo $trans[$i]['description'];
          echo '<br/><br/>';
      }
  }

  echo 'No transactions found<br/>';
  echo '<b>Total for all time:</b> &nbsp;$ ';
  echo number_format((($frm['ttype'] == 'deposit' or $frm['ttype'] == 'withdraw_pending') ? '-1' : '1') * $allsum, 2);
  echo '<br/>
<!--
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
-->
<br/>
<a href="?a=admin_main">Global Stats</a><br/>
<a href="?a=logout">Logout</a>

</p>
</card>
</wml>';
