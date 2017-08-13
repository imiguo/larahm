<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

echo '<b>Wire Transfer Details.</b><br>
<br>
';
  $id = sprintf('%d', $frm['id']);
  $q = 'select hm2_wires.*, date_format(hm2_wires.wire_date + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as wire_date1, hm2_users.username from hm2_wires, hm2_users where hm2_wires.id = '.$id.' and hm2_users.id = hm2_wires.user_id');
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  echo '
<form method=post name=nform >
<input type=hidden name=a value=wiredetails>';
  if (($frm['action'] == 'movetodeposit' or $frm['action'] == 'movetoaccount')) {
      echo '<input type=hidden name=action value="';
      echo $frm['action'];
      echo '">
<input type=hidden name=confirm value="yes">
<input type=hidden name=id value=';
      echo $id;
      echo '>';
  }

  echo '

<table cellspacing=1 cellpadding=2 border=0 width=500>
<tr>
 <td colspan=2><b>Wire information:</td>
</tr><tr>
 <td>Amount:</td>
 <td>';
  if (($frm['action'] != 'movetodeposit' and $frm['action'] != 'movetoaccount')) {
      echo number_format($row['amount'], 2);
  } else {
      echo '<input type=text name=amount value=\''.sprintf('%0.2f', $row['amount']).'\' class=inpts style=\'text-align: right;\'';
  }

  echo '</td>
</tr>';
  if ($frm['action'] != 'movetoaccount') {
      if (0 < $row['compound']) {
          echo '<tr>
      <td>Compounding percent:</td>
 <td>';
          echo number_format($row['compound'], 2);
          echo ' %</td>
</tr>';
      }
  }

  if (($frm['action'] == 'movetodeposit' or $frm['action'] == 'movetoaccount')) {
      echo '<tr>
      <td colspan=2> 
        ';
      echo start_info_table('70%');
      echo '        You can change the deposit amount here. 
        ';
      echo end_info_table();
      echo '      </td>
</tr>';
  }

  echo '<tr>
 <td>Date:</td>
 <td>';
  echo $row['wire_date1'];
  echo '</tr><tr>
 <td>Account:</td>
 <td>';
  echo $row['username'];
  echo '</td>
</tr>';
  if (($frm['action'] != 'movetodeposit' and $frm['action'] != 'movetoaccount')) {
      echo '<tr>
 <td colspan=2><br><b>Personal information</b></td>
</tr><tr>
 <td>Name:</td>
 <td>';
      echo $row['pname'];
      echo '</td>
</tr><tr>
 <td>Address:</td>
 <td>';
      echo $row['paddress'];
      echo '</td>
</tr><tr>
 <td>ZIP:</td>
 <td>';
      echo $row['pzip'];
      echo '</td>
</tr><tr>
 <td>City:</td>
 <td>';
      echo $row['pcity'];
      echo '</td>
</tr><tr>
 <td>State:</td>
 <td>';
      echo $row['pstate'];
      echo '</td>
</tr><tr>
 <td>Country:</td>
 <td>';
      echo $row['pcountry'];
      echo '</td>
</tr><tr>
 <td colspan=2><br><b>Bank information:</b><br></td>
</tr><tr>
 <td>Bank name:</td>
 <td>';
      echo $row['bname'];
      echo '</td>
</tr><tr>
 <td>Bank address:</td>
 <td>';
      echo $row['baddress'];
      echo '</td>
</tr><tr>
 <td>Bank zip:</td>
 <td>';
      echo $row['bzip'];
      echo '</td>
</tr><tr>
 <td>Bank city:</td>
 <td>';
      echo $row['bcity'];
      echo '</td>
</tr><tr>
 <td>Bank state:</td>
 <td>';
      echo $row['bstate'];
      echo '</td>
</tr><tr>
 <td>Bank country:</td>
 <td>';
      echo $row['bcountry'];
      echo '</td>
</tr><tr>
 <td>Bank account number:</td>
 <td>';
      echo $row['baccount'];
      echo '</td>
</tr><tr>
 <td>IBAN account:</td>
 <td>';
      echo $row['biban'];
      echo '</td>
</tr><tr>
 <td>SWIFT or BIC account:</td>
 <td>';
      echo $row['bswift'];
      echo '</td>
</tr>';
  }

  echo '</table>
<br>';
  if ($frm['action'] == 'movetoaccount') {
      echo '<input type=submit value="Add funds to account" class=sbmt>';
  } else {
      if ($frm['action'] != 'movetodeposit') {
          echo '<input type=button value="Move to deposit" class=sbmt onClick="document.location=\'?a=wiredetails&action=movetodeposit&id=';
          echo $row['id'];
          echo '\';"> &nbsp;
<input type=button value="Move to account" class=sbmt onClick="document.location=\'?a=wiredetails&action=movetoaccount&id=';
          echo $row['id'];
          echo '\';"> &nbsp;';
          if ($row['status'] == 'problem') {
              echo '<input type=button value="Move to new" class=sbmt onClick="document.location=\'?a=wiredetails&action=movetonew&id=';
              echo $row['id'];
              echo '\';"> &nbsp;';
          } else {
              echo '<input type=button value="Move to problem" class=sbmt onClick="document.location=\'?a=wiredetails&action=movetoproblem&id=';
              echo $row['id'];
              echo '\';"> &nbsp;';
          }

          echo '<input type=button value="Delete" class=sbmt onClick="if(confirm(\'Do you really want to delete this Wire Transfer?\')){document.location=\'?a=wiredetails&action=deletewire&id=';
          echo $row['id'];
          echo '\';}">';
      } else {
          echo '<input type=submit value="Create deposit" class=sbmt>';
      }
  }

  echo '</form>

<br>';
  echo start_info_table('100%');
  if ($frm['action'] == 'movetodeposit') {
      echo 'You can change the amount before moving this transfer to the deposit. ';
  } else {
      if ($frm['action'] == 'movetoaccount') {
          echo 'You can change the amount before moving this transfer to the account. ';
      } else {
          echo 'This screen helps you to manage Wire Transfers.<br>
Move to deposit - if you have really received this Wire Transfer, you can move 
this Wire to \'processed\', and create a deposit for this Wire Transfer.<br>
Move to \'problem\' - move this Wire Transfer to the \'problem\' Wires.<br>
Delete - delete this Wire Transfer if you have not received it. ';
      }
  }

  echo end_info_table();
