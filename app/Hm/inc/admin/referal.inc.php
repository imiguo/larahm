<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if ($settings['demomode'] == 1) {
    echo start_info_table('100%');
    echo '<b>Demo version restriction!</b><br>
You cannot edit referral settings!';
    echo end_info_table();
}

  echo '<s';
  echo 'cript language=javascript>
function checkref(a) {
  document.refform.elements["ref_name["+a+"]"].disabled = !document.refform.elements["active["+a+"]"].checked;
  document.refform.elements["ref_from["+a+"]"].disabled = !document.refform.elements["active["+a+"]"].checked;
  document.refform.elements["ref_to["+a+"]"].disabled = !document.refform.elements["active["+a+"]"].checked;
  document.refform.';
  echo 'elements["ref_percent["+a+"]"].disabled = !document.refform.elements["active["+a+"]"].checked;
//  document.refform.elements["ref_percent_daily["+a+"]"].disabled = !document.refform.elements["active["+a+"]"].checked;
//  document.refform.elements["ref_percent_weekly["+a+"]"].disabled = !document.refform.elements["active["+a+"]"].checked;
//  document.refform.elements["ref_percent_monthly["+a+"]"].';
  echo 'disabled = !document.refform.elements["active["+a+"]"].checked;
}
</script>

<b>Referral Settings:</b><br><br>
<form method=post name=refform>
<input type=hidden name=a value=referal>
<input type=hidden name=action value=change>

<input type=checkbox name=usereferal value=1 ';
  echo $settings['use_referal_program'] == 1 ? 'checked' : '';
  echo '> Use referral program?<br><br>
  ';
  echo start_info_table('100%');
  echo '  Toggle the usage of a referral program. Select if yes.
  ';
  echo end_info_table();
  echo '  <br>
  <br>
<input type=checkbox name=force_upline value=1 ';
  echo $settings['force_upline'] == 1 ? 'checked' : '';
  echo '>
  Force an upline during the signup.<br>
  <br>
  ';
  echo start_info_table('100%');
  echo '  Defines whether a user must have an upline to register.
  ';
  echo end_info_table();
  echo '  <br>
  <br>
<input type=checkbox name=get_rand_ref value=1 ';
  echo $settings['get_rand_ref'] == 1 ? 'checked' : '';
  echo '>
  Get a random upline (requires \'Force an upline during the signup\' option enabled)<br>
  <br>
  ';
  echo start_info_table('100%');
  echo '  If \'Force an upline during the signup\' option is enabled and user does not have an upline the system will get a random one.
  ';
  echo end_info_table();
  echo '  <br>
  <br>

<input type=checkbox name=show_refstat value=1 ';
  echo $settings['show_refstat'] == 1 ? 'checked' : '';
  echo '>
  Show the income/registerations statistics in the members area.<br>
  <br>
  ';
  echo start_info_table('100%');
  echo '  Check this checkbox for your users to see how many visitors were on your site
  and how many visitors became members.
  ';
  echo end_info_table();
  echo '  <br>
  <br>

<input type=checkbox name=show_referals value=1 ';
  echo $settings['show_referals'] == 1 ? 'checked' : '';
  echo '>
  Show referrals\' usernames and e-mail in the members area.<br>
  <br>
  ';
  echo start_info_table('100%');
  echo '  Check this checkbox if you want your users to see their referrals\' nicknames
  and e-mail.
  ';
  echo end_info_table();
  echo '  <br>
  <br>

  <table cellspacing=0 cellpadding=1 border=0>
   <tr>
    <td colspan=2>
     <input type=checkbox name=use_solid_referral_commission value=1 ';
  echo $settings['use_solid_referral_commission'] == 1 ? 'checked' : '';
  echo '> Pay solid referral commision
    </td>
   </tr>
   <tr>
    <td>Solid referral commision Amount: $</td>
    <td><input type=text name=solid_referral_commission_amount value="';
  echo $settings['solid_referral_commission_amount'];
  echo '" class=inpts></td>
   </tr>
  </table>
  <br>
  ';
  echo start_info_table('100%');
  echo '  Check this checkbox if you want your users receive solid referral comission after first referral deposit.<br>
  <b>If this option enabled no percentages uses and no refferals for 2-10 levels.</b>
  ';
  echo end_info_table();
  echo '  <br>
  <br>

<input type=checkbox name=payactivereferal value=1 ';
  echo $settings['pay_active_referal'] == 1 ? 'checked' : '';
  echo '>
  Pay referral commision to users who have made deposit.
  <br>
  ';
  echo start_info_table('100%');
  echo '  Check this checkbox and referral commision will pay to users who made a deposit only.
  ';
  echo end_info_table();
  echo '  <br>
  <br>

<input type=checkbox name=useactivereferal value=1 ';
  echo $settings['use_active_referal'] == 1 ? 'checked' : '';
  echo '>
  Count only the referrals who have made deposit.<br>
  <br>
  ';
  echo start_info_table('100%');
  echo '  Check this checkbox and referrals range will count from referrals who made a
  deposit only.
  ';
  echo end_info_table();
  echo '  <br>
  <br>

<b>Referral programs:</b><br>
<table cellspacing=1 cellpadding=1 border=0 width=100%>
<tr>
 <th bgcolor=FFEA00 colspan=2 rowspan=2>Program name</th>
 <th bgcolor=FFEA00 colspan=2>Referrals Range</th>
      <th bgcolor=FFEA00 colspan=4 rowspan=2>Commission (%)</th>
</tr>
<tr>
 <th bgcolor=FFEA00>From</th>
 <th bgcolor=FFEA00>To</th>
<!-- <th bgcolor=FFEA00>One time</th>
 <th bgcolor=FFEA00>Daily</th>
 <th bg';
  echo 'color=FFEA00>Weekly</th>
 <th bgcolor=FFEA00>Monthly</th>-->
</tr>';
  $q = 'select * from hm2_referal where level = 1 order by from_value';
  $sth = db_query($q);
  $num = 0;
  while ($row = mysql_fetch_array($sth)) {
      echo '<tr>
 <td><input type=checkbox name=active[';
      echo $num;
      echo '] value=1 checked onClick="checkref(';
      echo $num;
      echo ')"></td>
 <td><input type=text name=ref_name[';
      echo $num;
      echo '] value=\'';
      echo quote($row['name']);
      echo '\' class=inpts size=15></td>
 <td align=center><input type=text name=ref_from[';
      echo $num;
      echo '] class=inpts size=5 value=\'';
      echo $row['from_value'];
      echo '\' style="text-align: right"></td>
 <td align=center><input type=text name=ref_to[';
      echo $num;
      echo '] class=inpts size=5 value=\'';
      echo 0 < $row['to_value'] ? $row['to_value'] : 'and more';
      echo '\' style="text-align: right"></td>
 <td align=center><input type=text name=ref_percent[';
      echo $num;
      echo '] class=inpts size=6 value="';
      echo $row['percent'];
      echo '" style="text-align: right">%</td>

<!-- <td align=center><input type=text name=ref_percent_daily[';
      echo $num;
      echo '] class=inpts size=6 value="';
      echo $row['percent_daily'];
      echo '" style="text-align: right">%</td>
 <td align=center><input type=text name=ref_percent_weekly[';
      echo $num;
      echo '] class=inpts size=6 value="';
      echo $row['percent_weekly'];
      echo '" style="text-align: right">%</td>
 <td align=center><input type=text name=ref_percent_monthly[';
      echo $num;
      echo '] class=inpts size=6 value="';
      echo $row['percent_monthly'];
      echo '" style="text-align: right">%</td>
-->
</tr>';
      ++$num;
  }

  for ($i = 1; $i <= 3; ++$i) {
      echo '<tr>
 <td><input type=checkbox name=active[';
      echo $num;
      echo '] value=1 onClick="checkref(';
      echo $num;
      echo ')"></td>
 <td><input type=text name=ref_name[';
      echo $num;
      echo '] value=\'Add new\' class=inpts size=15></td>
 <td align=center><input type=text name=ref_from[';
      echo $num;
      echo '] class=inpts size=5 value=\'\' style="text-align: right"></td>
 <td align=center><input type=text name=ref_to[';
      echo $num;
      echo '] class=inpts size=5 value=\'\' style="text-align: right"></td>
 <td align=center><input type=text name=ref_percent[';
      echo $num;
      echo '] class=inpts size=6 style="text-align: right">%</td>
<!--
 <td align=center><input type=text name=ref_percent_daily[';
      echo $num;
      echo '] class=inpts size=6 style="text-align: right">%</td>
 <td align=center><input type=text name=ref_percent_weekly[';
      echo $num;
      echo '] class=inpts size=6 style="text-align: right">%</td>
 <td align=center><input type=text name=ref_percent_monthly[';
      echo $num;
      echo '] class=inpts size=6 style="text-align: right">%</td>
-->
</tr>';
      echo '<s';
      echo 'cript language=javascript>checkref(';
      echo $num;
      echo ');</script>';
      ++$num;
  }

  echo '</table>
<br>

<b>Other levels</b><br><br>';
  $i = 2;
  echo '<table cellspacing=1 cellpadding=1 border=0 width=50%>
<tr>
 <th bgcolor=FFEA00>Level</th>
      <th bgcolor=FFEA00>Commission (%)</th>
</tr>';
  for ($i = 2; $i < 11; ++$i) {
      echo '<tr>
 <td>';
      echo $i;
      echo ' level</td>
 <td align=center><input type=text name=ref';
      echo $i;
      echo '_cms class=inpts size=8 style="text-align: right" value="';
      echo $settings['ref'.$i.'_cms'];
      echo '">%</td>
</tr>';
  }

  echo '</table><br><br>

<input type=submit value="Change" class=sbmt>
</form>
<br><br>';
  echo start_info_table('100%');
  echo 'Change the referral program rates here.<br>
From and to - quantity of user\'s referrals.<br>
Commission - the referral percent. ';
  echo end_info_table();
  echo '<br>
<br>';
