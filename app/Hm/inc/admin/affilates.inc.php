<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$u_id = sprintf('%d', $frm['u_id']);
  $q = 'select * from hm2_users where id = '.$u_id;
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $username = $row['username'];
      $ref = $row['ref'];
  }

  if (0 < $ref) {
      $q = 'select * from hm2_users where id = '.$ref;
      $sth = db_query($q);
      while ($row = mysql_fetch_array($sth)) {
          $upline_name = $row['username'];
      }
  }

  $q = 'select count(*) as col from hm2_users where ref='.$u_id;
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $q_affilates = $row[col];
  }

  $q = 'select count(distinct user_id) as col from hm2_users, hm2_deposits where ref = '.$u_id.' and hm2_deposits.user_id = hm2_users.id';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $q_active_affilates = $row['col'];
  }

  echo '
<form method=post>
<input type=hidden name=a value=affilates>
<input type=hidden name=action value=\'change_upline\'>
<input type=hidden name=u_id value=\'';
  echo $u_id;
  echo '\'>
<b>Referrals:</b><br><br>

<table cellspacing=0 cellpadding=1 border=0>
<tr>
 <td>Username:</td>
 <td>';
  echo $username;
  echo '</td>
</tr><tr>
      <td>Referrals:</td>
 <td>';
  echo $q_affilates;
  echo '</td>
</tr>
    <tr>
      <td>Active referrals:</td>
 <td>';
  echo $q_active_affilates;
  echo '</td>
</tr>
<tr>
 <td>Upline:</td>
 <td><input type=text name=upline value=\'';
  echo quote($upline_name);
  echo '\' class=inpts></td>
</tr>
<tr>
 <td>&nbsp;</td>
 <td><input type=submit value="Change" class=sbmt></td>
</tr>
</table>
</form>
<br><br>

<table cellspacing=0 cellpadding=1 border=0>
<tr>
 <th>Username</th>
 <th>E-mail</th>
 <th>Status</th>
 <th>Del</th>
</tr>';
  $q_other_active = 0;
  $q_other = 0;
  $q = 'select * from hm2_users where ref='.$u_id.' order by id desc';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $row['stat'] = 'Not deposited yet';
      $q = 'select count(*) as col from hm2_deposits where user_id = '.$row[id];
      ($sth2 = db_query($q));
      while ($row2 = mysql_fetch_array($sth2)) {
          $row['stat'] = (0 < $row2['col'] ? 'Deposited' : 'Not deposited yet');
      }

      $parents = [$row['id']];
      $ref_stats = [];
      $i = 0;
      for ($i = 2; $i < 11; ++$i) {
          $parents_string = implode(',', $parents);
          $q_active = 0;
          $q = 'select id from hm2_users where ref in ('.$parents_string.')';
          $sth1 = db_query($q);
          $parents = [];
          while ($row1 = mysql_fetch_array($sth1)) {
              array_push($parents, $row1['id']);
              $q = 'select count(*) as col from hm2_deposits where user_id = '.$row1['id'];
              ($sth2 = db_query($q));
              while ($row2 = mysql_fetch_array($sth2)) {
                  $q_deposits = $row2[col];
              }

              if (0 < $q_deposits) {
                  ++$q_other_active;
                  ++$q_active;
              }

              ++$q_other;
          }

          if (!$parents) {
              break;
          }

          array_push($ref_stats, ['level' => $i - 1, 'cnt' => count($parents), 'cnt_active' => $q_active]);
      }

      echo '  <tr>
    <td>';
      echo $row['username'];
      echo '</td>
    <td><a href=mailto:';
      echo $row['email'];
      echo '>';
      echo $row['email'];
      echo '</a></td>
    <td align=center>';
      echo $row['stat'];
      echo '</td>
    <td align=center><a href=?a=affilates&action=remove_ref&u_id=';
      echo $u_id;
      echo '&ref=';
      echo $row[id];
      echo ' onClick="return confirm(\'Are you sure to delete this referral?\');">[X]</a></td>
  </tr>';
      if ($ref_stats) {
          echo '  <tr>
   <td colspan=4>User referrals:';
          for ($i = 0; $i < count($ref_stats); ++$i) {
              echo '<nobr>';
              echo $ref_stats[$i][cnt_active];
              echo ' active of ';
              echo $ref_stats[$i][cnt];
              echo ' on level ';
              echo $ref_stats[$i][level];
              echo $i < count($ref_stats) - 1 ? ';' : '';
              echo '</nobr>';
          }

          echo '   </td>
  </tr>';
          continue;
      }
  }

  echo '  <tr>
   <td colspan=4>&nbsp;</td>
  </tr>
  <tr>
   <td colspan=4><b>Total 2-10 level referrals:</b> ';
  echo $q_other;
  echo '</td>
  </tr>
  <tr>
   <td colspan=4><b>Total 2-10 level active referrals:</b> ';
  echo $q_other_active;
  echo '</td>
  </tr>';
