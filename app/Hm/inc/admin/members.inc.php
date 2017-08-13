<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$qonpage = 50;
  $qstatus = quote($frm['status']);
  if ($qstatus == '') {
      $qstatus = 'on';
  }

  $searchpart = '';
  if ($frm['q'] != '') {
      $qsearch = quote($frm['q']);
      $searchpart = ' and (username like \'%'.$qsearch.'%\' or email like \'%'.$qsearch.'%\' or name like \'%'.$qsearch.'%\') ';
  }

  $where_status = 'status = \''.$qstatus.'\'';
  if ($qstatus == 'blocked') {
      $where_status = 'activation_code != ""';
  }

  $q = 'select count(*) from hm2_users where '.$where_status.' and id <> 1 '.$searchpart.' order by id desc';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $total = $row[0];
  $page = sprintf('%d', $frm['p']);
  if ($page == 0) {
      $page = 1;
  }

  $qpages = ceil($total / $qonpage);
  if ($qpages < $page) {
      $page = $qpages;
  }

  $start = ($page - 1) * $qonpage;
  if ($start < -1) {
      $start = -1;
  }

  $end = $page * $qonpage;
  $end = ($total < $end ? $total : $end);
  $q = 'select *, date_format(date_register + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y\') as dr from hm2_users where '.$where_status.' and id <> 1 '.$searchpart.' order by id desc limit ').(0 < $start ? $start : 0).(''.', '.$qonpage);
  $sth = db_query($q);
  $members = [];
  while ($row = mysql_fetch_array($sth)) {
      $ar = get_user_balance($row['id']);
      $row = array_merge($row, $ar);
      array_push($members, $row);
  }

  echo '<s';
  echo 'cript>
function reverce(flag)
{
  d = document.members;
  for (i = 0; i < d.elements.length; i++)
  {
    if (d.elements[i].type == \'checkbox\')
    {
      d.elements[i].checked = flag; //!d.elements[i].checked;
    }
  }
}
</script>
<table cellspacing=0 cellpadding=0 border=0 width=100%>
<tr>
 <td><b>Members:</b></td>
 <td align=right>
<form method=get>
<input type=hidden name=a value=memb';
  echo 'ers>
	';
  echo '<s';
  echo 'elect name=status class=inpts>
		<option value=\'on\' ';
  echo $qstatus == 'on' ? 'selected' : '';
  echo '>Active
		<option value=\'off\' ';
  echo $qstatus == 'off' ? 'selected' : '';
  echo '>Disabled
		<option value=\'suspended\' ';
  echo $qstatus == 'suspended' ? 'selected' : '';
  echo '>Suspended
		<option value=\'blocked\' ';
  echo $qstatus == 'blocked' ? 'selected' : '';
  echo '>Blocked
	</select> <input type=submit value="Go" class=sbmt></form></td>
</tr>
<tr>
 <td colspan=2>
	<form method=get>
	<input type=hidden name=a value=members>
	<input type=hidden name=status value=\'';
  echo $qstatus;
  echo '\'>
	<input type=text name=q value=\'';
  echo quote($frm['q']);
  echo '\' class=inpts size=30> <input type=submit value="Search" class=sbmt>
	</form>
 </td>
</table>

<br>
<b>Results ';
  echo $start + 1;
  echo ' - ';
  echo $end;
  echo ' of ';
  echo $total;
  echo '</b><br>
<form method=post name=members>
<input type=hidden name=a value=members>
<input type=hidden name=action value=modify_status>

<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr>
 <th bgcolor=FFEA00 align=center>NickName</th>
 <th bgcolor=FFEA00 align=center width=100>Reg.Date</th>
 <th bgcolor=FFEA00 align=center>Status</th>
 <th bgcolor=FFEA00 align=center>Account</th>
 <th bgcolor=F';
  echo 'FEA00 align=center>Deposit</th>
 <th bgcolor=FFEA00 align=center>Earned</th>
      <th bgcolor=FFEA00 align=center>Withdrew</th>
</tr>';
  if (0 < count($members)) {
      for ($i = 0; $i < count($members); ++$i) {
          echo '<tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
 <td>';
          if ($qstatus == 'blocked') {
              echo ' <input type="checkbox" name="activate[';
              echo $members[$i]['id'];
              echo ']" value=1>';
          }

          echo '   ';
          echo '<s';
          echo 'mall>';
          echo $members[$i]['username'];
          echo '</small></td>
 <td align=center width=100>';
          echo '<s';
          echo 'mall>';
          echo $members[$i]['dr'];
          echo ' ';
          echo $members[$i]['confirm_string'] != '' ? '<br>not confirmed!' : '';
          echo '</small></td>
 <td>';
          echo '<s';
          echo 'elect name="active[';
          echo $members[$i]['id'];
          echo ']" class=inpts>
	<option value=\'on\' ';
          echo $members[$i]['status'] == 'on' ? 'selected' : '';
          echo '>Active
	<option value=\'off\' ';
          echo $members[$i]['status'] == 'off' ? 'selected' : '';
          echo '>Disabled
	<option value=\'suspended\' ';
          echo $members[$i]['status'] == 'suspended' ? 'selected' : '';
          echo '>Suspended</select>
 </td>
 <td align=right>';
          echo '<s';
          echo 'mall>$';
          echo number_format($members[$i]['total'], 2);
          echo '</small></td>
 <td align=right>';
          echo '<s';
          echo 'mall>$';
          echo number_format(abs($members[$i]['deposit']), 2);
          echo '</small></td>
 <td align=right>';
          echo '<s';
          echo 'mall>$';
          echo number_format($members[$i]['earning'], 2);
          echo '</small></td>
 <td align=right>';
          echo '<s';
          echo 'mall>$';
          echo number_format(abs($members[$i]['withdrawal']), 2);
          echo '</small></td>
</tr>
<tr>
 <td colspan=7 align=right>';
          echo '<s';
          echo 'mall>
	<a href=?a=editaccount&id=';
          echo $members[$i]['id'];
          echo '>[edit]</a>
	<a href="?a=deleteaccount&id=';
          echo $members[$i]['id'];
          echo '&p=';
          echo $frm['p'];
          echo '&q=';
          echo $frm['q'];
          echo '&status=';
          echo $frm['status'];
          echo '" onclick="return confirm(\'Are you sure you want to delete this user?\');">[delete]</a>
	<a href=\'mailto:';
          echo quote($members[$i]['email']);
          echo '\'>[e-mail]</a>
	<a href=?a=userfunds&id=';
          echo $members[$i]['id'];
          echo '>[manage funds]</a></small>
 </td>
</tr>';
          if ($qstatus == 'blocked') {
              echo '<tr>
<td colspan=7><a href=javascript:reverce(true)>Select all</a> / <a href=javascript:reverce(false)>Unselect all</a></td>
</tr>';
              continue;
          }
      }
  } else {
      echo '<tr>
 <td colspan=7 align=center>No accounts found</td>
</tr>';
  }

  echo '</table><br>';
  if (1 < $qpages) {
      echo '<center>';
      echo '<s';
      echo 'mall>';
      for ($i = 1; $i <= $qpages; ++$i) {
          if ($page == $i) {
              echo ' ['.$i.'] ';
              continue;
          } else {
              echo ' <a href="?a=members&status=';
              echo $qstatus;
              echo '&q=';
              echo $frm['q'];
              echo '&p=';
              echo $i;
              echo '">';
              echo $i;
              echo '</a> ';
              continue;
          }
      }

      echo '</small></center>';
  }

  if ($qstatus == 'blocked') {
      echo '<input type=button value="Activate" class=sbmt onclick="document.members.action.value=\'activate\';document.members.submit()"> &nbsp;';
  }

  echo '<input type=submit value="Modify" class=sbmt> &nbsp; <input type=button value="Add a new member" class=sbmt onClick="document.location=\'?a=addmember\';">
</form>

<br>';
  echo start_info_table('100%');
  echo 'Members list:<br><br>
Members list splits your members to 3 types: Active, Suspended and Disabled.<br>
Active: User can login and receive earnings if deposited in the system.<br>
Suspended: User can login, but cannot not receive any earnings from deposits.<br>
Disabled: User can not login and cannot receive any earnings.<br>
<br>
The top left search form helps you to search a user by the nickname o';
  echo 'r e-mail.
You can also enter a part of a nickname or e-mail to search users.<br>
The top right form helps you to navigate between the user types.<br>
You can see the following information in the members list:<br>
Nickname, Registration date, Status, Account, Deposit, Earned, Withdrew. You can
see not confirmed users also if you use double opt-in registration.<br>
<br>

Actions:<br>
Change sta';
  echo 'tus: select a new status in the \'Status\' row and click the \'Modify\'
button;<br>
Edit user information: click on the \'edit\' link;<br>
Delete user: click on the \'delete\' link and confirm this action;<br>
Send e-mail to user: click on the \'e-mail\' link and send e-mail to user.<br>
\'Manage funds\' link will help you to check any user\'s history and change his funds.<br>
Add a new Member: click on the ';
  echo '"Add a new member" button. You\'ll see the form
for adding a new member. ';
  echo end_info_table();
