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
  if (app('data')->frm['action'] == 'forgot_password') {
      $found_records = 0;
      $email = quote(app('data')->frm['email']);
      $q = 'select * from users where username=\''.$email.'\' or email=\''.$email.'\' and (status=\'on\' or status=\'suspended\')';
      if (! ($sth = db_query($q))) {
      }

      while ($row = mysql_fetch_array($sth)) {
          if ((app('data')->settings['demomode'] == 1 and $row['id'] <= 3)) {
          } else {
              if ($row['activation_code'] != '') {
                  $info = [];
                  $info['activation_code'] = $row['activation_code'];
                  $info['username'] = $row['username'];
                  $info['name'] = $row['name'];
                  $info['ip'] = '[not logged]';
                  $info['max_tries'] = app('data')->settings['brute_force_max_tries'];
                  send_template_mail('brute_force_activation', $row['email'], app('data')->settings['system_email'], $info);
              }

              $password = gen_confirm_code(8, 0);
              $enc_password = bcrypt($password);
              $q = 'update users set password = \''.$enc_password.'\' where id = '.$row['id'];
              if (! ($sth1 = db_query($q))) {
              }

              if (app('data')->settings['store_uncrypted_password'] == 1) {
                  $pswd = quote($password);
                  $q = 'update users set pswd = \''.$pswd.'\' where id = '.$row['id'];
                  if (! ($sth1 = db_query($q))) {
                  }
              }

              $info = [];
              $info['username'] = $row['username'];
              $info['password'] = $password;
              $info['name'] = $row['name'];
              $info['ip'] = app('data')->env['REMOTE_ADDR'];
              send_template_mail('forgot_password', $row['email'], app('data')->settings['system_email'], $info);
          }

          $found_records = 1;
      }
  }

  view_assign('found_records', $found_records);
  view_execute('forgot_password.blade.php');
