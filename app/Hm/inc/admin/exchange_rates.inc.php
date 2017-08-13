<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if ($settings['demomode']) {
    echo start_info_table('100%');
    echo '<b>Demo version restriction!</b><br>
You cannot change the exchange rates!';
    echo end_info_table();
}

  $exch = [];
  $q = 'select * from hm2_exchange_rates';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $exch[$row['sfrom']][$row['sto']] = $row['percent'];
  }

  echo '
<form method=post>
<input type=hidden name=a value=exchange_rates>
<input type=hidden name=action value=save>
<b>Exchange Rates:</b><br><br>

<table cellspacing=0 cellpadding=2 border=0 width=100%><tr><td valign=top>

<table cellspacing=0 cellpadding=0 border=0><tr><td valign=top bgcolor=#FF8D00>
<table cellspacing=1 cellpadding=2 border=0>
<tr>
  <td bgcolor=#FFFFFF nowrap align=center>From / To</td>';

  foreach ($exchange_systems as $id => $value) {
      echo '  <td bgcolor=#FFFFFF align=center><img src=images/';
      echo $id;
      echo '.gif height=17></td>';
  }

  echo '</tr>';
  foreach ($exchange_systems as $id_from => $value) {
      echo '<tr>
  <td align=center bgcolor=#FFFFFF><img src=images/';
      echo $id_from;
      echo '.gif height=17></td>
  ';
      foreach ($exchange_systems as $id_to => $value) {
          echo '    <td align=center bgcolor=#FFFFFF>';
          if ($id_from != $id_to) {
              echo '<input type="text" name="exch[';
              echo $id_from;
              echo '][';
              echo $id_to;
              echo ']" value="';
              echo sprintf('%.02f', $exch[$id_from][$id_to]);
              echo '" size=5 class=inpts>';
          } else {
              echo ' N/A ';
          }

          echo '</td>
  ';
      }

      echo '</tr>';
  }

  echo '</table>
</td></tr></table>
<br>
<input type=submit value="Update" class=sbmt>

</td>
      <td valign=top align=right>
        ';
  echo start_info_table('300');
  echo '        Exchange Rates:<br>
        <br>
        Figures are the percents of an exchange rates.<br>
        Vertical column is FROM currency.<br>
        Horizontal row is TO currency.<br>
        <br>
        Ex: To set a percent for e-gold to INTGold exchange you should
        edit the field in the vertical column with the e-gold icon and in the row with the INTGold one.<br> <br>
        To di';
  echo 'sable an exchange set its percentage to 100.
        ';
  echo end_info_table();
  echo '      </td>
    </tr></table>
</form>
<br><br>';
