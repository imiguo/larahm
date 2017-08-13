<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$success = 0;
  if ($frm['c'] != '') {
      $info = [];
      $conf_string = quote($frm['c']);
      $q = 'select * from hm2_users where confirm_string = \''.$conf_string.'\'';
      if (!($sth = db_query($q))) {
      }

      while ($row = mysql_fetch_array($sth)) {
          $success = 1;
          $info['username'] = $row['username'];
          $info['password'] = '********';
          $info['name'] = $row['name'];
          $info['email'] = $row['email'];
          $info['ref'] = $row['ref'];
      }

      if ($success == 1) {
          $q = 'update hm2_users set confirm_string = \'\' where confirm_string = \''.$conf_string.'\'';
          if (!(db_query($q))) {
          }

          send_template_mail('registration', $info['email'], $settings['opt_in_email'], $info);
          $ref = quote($info['ref']);
          $q = 'select * from hm2_users where id = \''.$ref.'\'';
          $sth = db_query($q);
          while ($refinfo = mysql_fetch_array($sth)) {
              $refminfo = [];
              $refminfo['name'] = $refinfo['name'];
              $refminfo['username'] = $refinfo['username'];
              $refminfo['ref_username'] = $info['username'];
              $refminfo['ref_name'] = $info['name'];
              $refminfo['ref_email'] = $info['email'];
              send_template_mail('direct_signup_notification', $refinfo['email'], $settings['opt_in_email'], $refminfo);
          }
      }
  }

  $smarty->assign('success', $success);
  $smarty->display('confirm_registration.tpl');
