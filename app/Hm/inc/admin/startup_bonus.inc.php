<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

echo '<s';
  echo 'cript language=javascript>
function en_dis() {
  document.nform.ec.disabled = !document.nform.add_startup_bonus.checked;
  document.nform.startup_bonus.disabled = !document.nform.add_startup_bonus.checked;
}
</script>
<form method=post name=nform >
<input type=hidden name=a value="startup_bonus">
<input type=hidden name=act value="set">
<table cellspacing=1 cellpadding=2 border=0>
<tr>
      ';
  echo '<td colspan=2><b>Startup bonus:</b></td>
</tr><tr>
 <td colspan=2><input type=checkbox name=add_startup_bonus value=1 ';
  echo 0 < $settings['startup_bonus'] ? 'checked' : '';
  echo ' onclick="en_dis()"> Enable startup bonus</td>
</tr><tr>
 <td>Amount:</td>
 <td><input type=text name=\'startup_bonus\' value="';
  echo sprintf('%0.2f', $settings[startup_bonus]);
  echo '" class=inpts style="text-align: right"></td>
</tr>
 <td>Currency:</td>
 <td>
 ';
  echo '<s';
  echo 'elect name=ec class=inpts>';
  foreach ($exchange_systems as $id => $data) {
      if ($data['status'] != 1) {
          continue;
      }
      echo '	<option value="';
      echo $id;
      echo '" ';
      echo $id == $settings['startup_bonus_ec'] ? 'selected' : '';
      echo '>';
      echo $data['name'];
  }

  echo ' </select>
 </td>
</tr><tr>
 <td colspan=2>&nbsp;</td>
</tr><tr>
 <td colspan=2><input type=checkbox name=forbid_withdraw_before_deposit value=1 ';
  echo 0 < $settings['forbid_withdraw_before_deposit'] ? 'checked' : '';
  echo '> Forbid withdrawal till deposit</td>
</tr><tr>
 <td>&nbsp;</td>
 <td><input type=submit value="Save" class=sbmt></td>
</tr>
</table>

</form>';
  echo '<s';
  echo 'cript language=javascript>
en_dis();
</script>
';
  echo start_info_table('100%');
  echo 'You can add startup bonus for every user that register at your program.<br>
This bonus will be added after registration only, and we recommend disable withdraw before user invest some money to your program. (this settings available at \'settings\' screen).';
  echo end_info_table();
  echo '</p>';
