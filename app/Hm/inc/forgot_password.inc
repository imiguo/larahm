<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$found_records = -1;
  if ($frm['action'] == 'forgot_password') {
      $found_records = 0;
      $email = quote($frm['email']);
      $q = 'select * from hm2_users where username=\''.$email.'\' or email=\''.$email.'\' and (status=\'on\' or status=\'suspended\')';
      if (!($sth = db_query($q))) {
      }

      while ($row = mysql_fetch_array($sth)) {
          if (($settings['demomode'] == 1 and $row['id'] <= 3)) {
          } else {
              if ($row['activation_code'] != '') {
                  $info = [];
                  $info['activation_code'] = $row['activation_code'];
                  $info['username'] = $row['username'];
                  $info['name'] = $row['name'];
                  $info['ip'] = '[not logged]';
                  $info['max_tries'] = $settings['brute_force_max_tries'];
                  send_template_mail('brute_force_activation', $row['email'], $settings['system_email'], $info);
              }

              $password = gen_confirm_code(8, 0);
              $enc_password = md5($password);
              $q = 'update hm2_users set password = \''.$enc_password.'\' where id = '.$row['id'];
              if (!($sth1 = db_query($q))) {
              }

              if ($settings['store_uncrypted_password'] == 1) {
                  $pswd = quote($password);
                  $q = 'update hm2_users set pswd = \''.$pswd.'\' where id = '.$row['id'];
                  if (!($sth1 = db_query($q))) {
                  }
              }

              $info = [];
              $info['username'] = $row['username'];
              $info['password'] = $password;
              $info['name'] = $row['name'];
              $info['ip'] = $frm_env['REMOTE_ADDR'];
              send_template_mail('forgot_password', $row['email'], $settings['system_email'], $info);
          }

          $found_records = 1;
      }
  }

  $smarty->assign('found_records', $found_records);
  $smarty->display('forgot_password.tpl');
