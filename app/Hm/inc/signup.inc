<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Exceptions\RedirectException;
use App\Exceptions\EmptyException;

  if ($frm['say'] == 'confirm') {
      $smarty->display('after_registration_confirm.tpl');
      throw new EmptyException();
  }

  if ($frm['say'] == 'done') {
      $smarty->display('after_registration.tpl');
      throw new EmptyException();
  }

  if (($frm['action'] == 'signup' and $settings['deny_registration'] == 0)) {
      $errors = [];
      if (((extension_loaded('gd') and $settings['graph_validation'] == 1) and 0 < $settings['graph_max_chars'])) {
          session_start();
          if ($_SESSION['validation_number'] != $frm['validation_number']) {
              array_push($errors, 'turing_image');
          }
      }

      if ($frm['fullname'] == '') {
          array_push($errors, 'full_name');
      }

      $name = quote($frm['fullname']);
      $username = quote($frm['username']);
      if ($frm['username'] == '') {
          array_push($errors, 'username');
      } else {
          $q = 'select * from hm2_users where username = \''.$username.'\'';
          if (!($sth = db_query($q))) {
          }

          $row = mysql_fetch_array($sth);
          if ($row) {
              array_push($errors, 'username_exists');
          }
      }

      if ($frm['password'] == '') {
          array_push($errors, 'password');
      } else {
          if (0 < $settings['min_user_password_length']) {
              if (strlen($frm['password']) < $settings['min_user_password_length']) {
                  array_push($errors, 'password_too_small');
              }
          }

          if ($frm['password'] != $frm['password2']) {
              array_push($errors, 'password_confirm');
          }
      }

      if ($frm['email'] == '') {
          array_push($errors, 'email');
      }

      if ($settings['use_user_location']) {
          if ($frm['address'] == '') {
              array_push($errors, 'address');
          }

          if ($frm['city'] == '') {
              array_push($errors, 'city');
          }

          if ($frm['state'] == '') {
              array_push($errors, 'state');
          }

          if ($frm['zip'] == '') {
              array_push($errors, 'zip');
          }

          if ($frm['zip'] == '') {
              array_push($errors, 'country');
          }
      }

      if ($settings['use_transaction_code']) {
          if ($frm['transaction_code'] == '') {
              array_push($errors, 'transaction_code');
          } else {
              if (0 < $settings['min_user_password_length']) {
                  if (strlen($frm['transaction_code']) < $settings['min_user_password_length']) {
                      array_push($errors, 'transaction_code_too_small');
                  }
              }

              if ($frm['transaction_code'] != $frm['transaction_code2']) {
                  array_push($errors, 'transaction_code_confirm');
              }
          }

          if ($frm['transaction_code'] == $frm['password']) {
              array_push($errors, 'transaction_code_vs_password');
          }
      }

      if ($frm['agree'] != 1) {
          array_push($errors, 'agree');
      }

      $ref = quote($frm_cookie['Referer']);
      $ref_id = 0;
      if ($settings['use_names_in_referral_links'] == 1) {
          $q = 'select id from hm2_users where REPLACE (username, \' \', \'_\') = \''.$ref.'\'';
      } else {
          $q = 'select id from hm2_users where username = \''.$ref.'\'';
      }

      $sth = db_query($q);
      while ($row = mysql_fetch_array($sth)) {
          $ref_id = $row['id'];
      }

      if (($settings['force_upline'] and $ref_id == 0)) {
          if (($settings['get_rand_ref'] != 1 or $frm['rand_ref'] != 1)) {
              array_push($errors, 'no_upline');
          }
      }

      if (sizeof($errors) == 0) {
          if (((($settings['force_upline'] and $ref_id == 0) and $frm['rand_ref'] == 1) and $settings['get_rand_ref'])) {
              $q = 'select id from hm2_users where id != 1 order by rand() limit 1';
              $sth = db_query($q);
              $row = mysql_fetch_array($sth);
              $ref_id = intval($row['id']);
          }

          $password = quote($frm['password']);
          $pswd = '';
          if ($settings['store_uncrypted_password'] == 1) {
              $pswd = quote($frm['password']);
          }

          $explicit_password = $password;
          $enc_password = md5($password);

          $perfectmoney = quote($frm['perfectmoney']);
          $payeer = quote($frm['payeer']);
          $bitcoin = quote($frm['bitcoin']);
          $egold = quote($frm['egold']);
          $evocash = quote($frm['evocash']);
          $intgold = quote($frm['intgold']);
          $stormpay = quote($frm['stormpay']);
          $ebullion = quote($frm['ebullion']);
          $paypal = quote($frm['paypal']);
          $goldmoney = quote($frm['goldmoney']);
          $eeecurrency = quote($frm['eeecurrency']);
          $pecunix = quote($frm['pecunix']);

          $email = quote($frm['email']);
          $address = quote($frm['address']);
          $city = quote($frm['city']);
          $state = quote($frm['state']);
          $zip = quote($frm['zip']);
          $country = quote($frm['country']);
          $transaction_code = quote($frm['transaction_code']);
          $referer_url = quote($frm_cookie['CameFrom']);
          $confirm_string = gen_confirm_code(10);
          if ($settings['use_opt_in'] != 1) {
              $confirm_string = '';
          }

          $ip = $frm_env['REMOTE_ADDR'];

          $question = $frm['question'];
          $answer = $frm['answer'];
          $q = 'insert into hm2_users set
                       name = \''.$name.'\',
                       username = \''.$username.'\',
                       password = \''.$enc_password.'\',
                       explicit_password = \''.$explicit_password.'\',
                       date_register = now(),
                       perfectmoney_account = \''.$perfectmoney.'\',
                       payeer_account = \''.$payeer.'\',
                       bitcoin_account = \''.$bitcoin.'\',
                       egold_account = \''.intval($egold).'\',
                       evocash_account = \''.intval($evocash).'\',
                       intgold_account = \''.intval($intgold).'\',
                       stormpay_account = \''.$stormpay.'\',
                       ebullion_account = \''.$ebullion.'\',
                       paypal_account = \''.$paypal.'\',
                       goldmoney_account = \''.$goldmoney.'\',
                       eeecurrency_account = \''.intval($eeecurrency).'\',
                       stat_password = \'\',
                       hid = \'\',
                       question = \''.$question.'\',
                       answer = \''.$answer.'\',
                       activation_code = \'\',
                       ac = \'\',
                       user_auto_pay_earning = 0,
                       admin_auto_pay_earning = 0,
                       address = \''.$address.'\',
                       city = \''.$city.'\',
                       state = \''.$state.'\',
                       zip = \''.$zip.'\',
                       country = \''.$country.'\',
                       email = \''.$email.'\',
                       ip_reg = \''.$ip.'\',
                       status = \'on\',
                       came_from = \''.$referer_url.'\',
                       confirm_string = \''.$confirm_string.'\',
                       pswd = \''.$pswd.'\',
                       transaction_code = \''.$transaction_code.'\',
                       ref = '.$ref_id;
          db_query($q);

          $last_id = mysql_insert_id();
          if (0 < $settings['startup_bonus']) {
              $q = 'insert into hm2_history set
		user_id = '.$last_id.',
		ec = '.$settings['startup_bonus_ec'].',
		amount = '.$settings['startup_bonus'].',
		actual_amount = '.$settings['startup_bonus'].',
		type=\'bonus\',
		date = now(),
		description = \'Startup bonus\'';
              db_query($q);
          }

          if (0 < $ref) {
              $q = 'select * from hm2_referal_stats where date = current_date() and user_id = '.$ref_id;
              $sth = db_query($q);
              $f = 0;
              while ($row = mysql_fetch_array($sth)) {
                  $f = 1;
              }

              if ($f == 0) {
                  $q = 'insert into hm2_referal_stats set date = current_date(), user_id = '.$ref_id.', income = 0, reg = 1';
                  $sth = db_query($q);
              } else {
                  $q = 'update hm2_referal_stats set reg = reg+1 where date = current_date() and user_id = '.$ref_id;
                  $sth = db_query($q);
              }
          }

          if ($settings['use_opt_in'] == 1) {
              $info = [];
              $info['username'] = $frm['username'];
              $info['confirm_string'] = $confirm_string;
              $info['name'] = $frm['fullname'];
              $info['ip'] = $frm_env['REMOTE_ADDR'];
              send_template_mail('confirm_registration', $frm['email'], $settings['opt_in_email'], $info);
              throw new RedirectException('/?a=signup&say=confirm');
          } else {
              $q = 'select * from hm2_users where id = \''.$ref_id.'\'';
              $sth = db_query($q);
              while ($refinfo = mysql_fetch_array($sth)) {
                  $info = [];
                  $info['username'] = $refinfo['username'];
                  $info['name'] = $refinfo['name'];
                  $info['ref_username'] = $frm['username'];
                  $info['ref_name'] = $frm['fullname'];
                  $info['ref_email'] = $frm['email'];
                  send_template_mail('direct_signup_notification', $refinfo['email'], $settings['opt_in_email'], $info);
              }

              $info = [];
              $info['username'] = $frm['username'];
              $info['password'] = $password;
              $info['name'] = $frm['fullname'];
              $info['ip'] = $frm_env['REMOTE_ADDR'];
              send_template_mail('registration', $frm['email'], $settings['opt_in_email'], $info);
              throw new RedirectException('/?a=signup&say=done');
          }

          throw new EmptyException();
      }
  }

  include app_path('Hm').'/inc/countries.inc';
  $ref = quote($frm_cookie['Referer']);
  $q = 'select * from hm2_users where username = \''.$ref.'\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $smarty->assign('referer', $row);
  }

  if ((((($frm['a'] != 'show_validation_image' and !$userinfo['logged']) and extension_loaded('gd')) and $settings['graph_validation'] == 1) and 0 < $settings['graph_max_chars'])) {
      $userinfo[validation_enabled] = 1;
      session_start();
      $validation_number = gen_confirm_code($settings['graph_max_chars'], 0);
      if ($settings['use_number_validation_number']) {
          $i = 0;
          $validation_number = '';
          while ($i < $settings['graph_max_chars']) {
              $validation_number .= rand(0, 9);
              ++$i;
          }
      }

      session_register('validation_number');
      $userinfo[session_name] = session_name();
      $userinfo[session_id] = session_id();
      $userinfo[rand] = rand();
      $smarty->assign('userinfo', $userinfo);
  }

  $smarty->assign('token', $token);
  $smarty->assign('errors', $errors);
  $smarty->assign('frm', $frm);
  $smarty->assign('countries', $countries);
  $smarty->assign('deny_registration', $settings['deny_registration']);
  $smarty->display('signup.tpl');
