<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

  if ($frm['say'] == 'invalid_passphrase') {
      echo '<b style="color:red">Invalid Alternative Passphrase. No data has been updated.</b><br><br>';
  }

  if ($frm['say'] == 'done') {
      echo '<b style="color:green">Changes have been successfully updated.</b><br>
<br>';
  }

  if ($userinfo['logged'] == 0) {
      exit();
  }

  echo '
<b>Advanced login security settings:</b><br><br>

<form method=post>
<input type=hidden name=a value="change_login_security">
<input type=hidden name=act value="change">
Detect IP Address Change Sensitivity<br>
<input type=radio name=ip value=disabled ';
  echo $acsent_settings['detect_ip'] == 'disabled' ? 'checked' : 'ddd';
  echo '>Disabled<br>
<input type=radio name=ip value=medium ';
  echo $acsent_settings['detect_ip'] == 'medium' ? 'checked' : '';
  echo '>Medium<br>
<input type=radio name=ip value=high ';
  echo $acsent_settings['detect_ip'] == 'high' ? 'checked' : '';
  echo '>High<br><br>

Detect Browser Change<br>
<input type=radio name=browser value=disabled ';
  echo $acsent_settings['detect_browser'] == 'disabled' ? 'checked' : '';
  echo '>Disabled<br>
<input type=radio name=browser value=enabled ';
  echo $acsent_settings['detect_browser'] == 'enabled' ? 'checked' : '';
  echo '>Enabled<br><br>
E-mail:<br>
<input type=text name=email value="';
  echo $acsent_settings['email'];
  echo '" class=inpts size=50><br>
<input type=submit value="Set" class=sbmt>
</form>
<hr>
  <br><br><br>';
  $dirs = [];
  if (!file_exists('./inc/.htaccess')) {
      array_push($dirs, './inc');
  }

  if (!file_exists('./tmpl/.htaccess')) {
      array_push($dirs, './tmpl');
  }

  if (!file_exists(CACHE_PATH.'/.htaccess')) {
      array_push($dirs, CACHE_PATH);
  }

  if (0 < count($dirs)) {
      echo '
<b>Security note:</b><br><br>
Please upload the .htaccess file to the following folders:<br>';
      for ($i = 0; $i < count($dirs); ++$i) {
          echo '<li>'.$dirs[$i].'</li>';
      }

      echo 'You can find the .htaccess files in the latest archive with the hyip manager script.<br>';
  }
  echo '</form>
<hr>
<br><br>';
