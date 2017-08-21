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
use Illuminate\Support\Facades\Cookie;

if (app('data')->frm['say'] == 'confirm') {
      view_execute('after_registration_confirm.blade.php');
      throw new EmptyException();
  }

  if (app('data')->frm['say'] == 'done') {
      view_execute('after_registration.blade.php');
      throw new EmptyException();
  }

  if ((app('data')->frm['action'] == 'signup' and app('data')->settings['deny_registration'] == 0)) {
      $errors = [];
      if (app('data')->frm['fullname'] == '') {
          array_push($errors, 'full_name');
      }

      $name = quote(app('data')->frm['fullname']);
      $username = quote(app('data')->frm['username']);
      if (app('data')->frm['username'] == '') {
          array_push($errors, 'username');
      } else {
          $q = 'select * from users where username = \''.$username.'\'';
          if (!($sth = db_query($q))) {
          }

          $row = mysql_fetch_array($sth);
          if ($row) {
              array_push($errors, 'username_exists');
          }
      }

      if (app('data')->frm['password'] == '') {
          array_push($errors, 'password');
      } else {
          if (0 < app('data')->settings['min_user_password_length']) {
              if (strlen(app('data')->frm['password']) < app('data')->settings['min_user_password_length']) {
                  array_push($errors, 'password_too_small');
              }
          }

          if (app('data')->frm['password'] != app('data')->frm['password2']) {
              array_push($errors, 'password_confirm');
          }
      }

      if (app('data')->frm['email'] == '') {
          array_push($errors, 'email');
      }

      if (app('data')->settings['use_user_location']) {
          if (app('data')->frm['address'] == '') {
              array_push($errors, 'address');
          }

          if (app('data')->frm['city'] == '') {
              array_push($errors, 'city');
          }

          if (app('data')->frm['state'] == '') {
              array_push($errors, 'state');
          }

          if (app('data')->frm['zip'] == '') {
              array_push($errors, 'zip');
          }

          if (app('data')->frm['zip'] == '') {
              array_push($errors, 'country');
          }
      }

      if (app('data')->settings['use_transaction_code']) {
          if (app('data')->frm['transaction_code'] == '') {
              array_push($errors, 'transaction_code');
          } else {
              if (0 < app('data')->settings['min_user_password_length']) {
                  if (strlen(app('data')->frm['transaction_code']) < app('data')->settings['min_user_password_length']) {
                      array_push($errors, 'transaction_code_too_small');
                  }
              }

              if (app('data')->frm['transaction_code'] != app('data')->frm['transaction_code2']) {
                  array_push($errors, 'transaction_code_confirm');
              }
          }

          if (app('data')->frm['transaction_code'] == app('data')->frm['password']) {
              array_push($errors, 'transaction_code_vs_password');
          }
      }

      if (app('data')->frm['agree'] != 1) {
          array_push($errors, 'agree');
      }

      $ref = Cookie::get('referer');
      $ref_id = 0;
      if (app('data')->settings['use_names_in_referral_links'] == 1) {
          $q = 'select id from users where REPLACE (username, \' \', \'_\') = \''.$ref.'\'';
      } else {
          $q = 'select id from users where username = \''.$ref.'\'';
      }

      $sth = db_query($q);
      while ($row = mysql_fetch_array($sth)) {
          $ref_id = $row['id'];
      }

      if ((app('data')->settings['force_upline'] and $ref_id == 0)) {
          if ((app('data')->settings['get_rand_ref'] != 1 or app('data')->frm['rand_ref'] != 1)) {
              array_push($errors, 'no_upline');
          }
      }

      if (sizeof($errors) == 0) {
          if ((((app('data')->settings['force_upline'] and $ref_id == 0) and app('data')->frm['rand_ref'] == 1) and app('data')->settings['get_rand_ref'])) {
              $q = 'select id from users where id != 1 order by rand() limit 1';
              $sth = db_query($q);
              $row = mysql_fetch_array($sth);
              $ref_id = intval($row['id']);
          }

          $password = quote(app('data')->frm['password']);
          $pswd = '';
          if (app('data')->settings['store_uncrypted_password'] == 1) {
              $pswd = quote(app('data')->frm['password']);
          }

          $explicit_password = $password;
          $enc_password = bcrypt($password);

          $perfectmoney = quote(app('data')->frm['perfectmoney']);
          $payeer = quote(app('data')->frm['payeer']);
          $bitcoin = quote(app('data')->frm['bitcoin']);
          $egold = quote(app('data')->frm['egold']);
          $evocash = quote(app('data')->frm['evocash']);
          $intgold = quote(app('data')->frm['intgold']);
          $stormpay = quote(app('data')->frm['stormpay']);
          $ebullion = quote(app('data')->frm['ebullion']);
          $paypal = quote(app('data')->frm['paypal']);
          $goldmoney = quote(app('data')->frm['goldmoney']);
          $eeecurrency = quote(app('data')->frm['eeecurrency']);
          $pecunix = quote(app('data')->frm['pecunix']);

          $email = quote(app('data')->frm['email']);
          $address = quote(app('data')->frm['address']);
          $city = quote(app('data')->frm['city']);
          $state = quote(app('data')->frm['state']);
          $zip = quote(app('data')->frm['zip']);
          $country = quote(app('data')->frm['country']);
          $transaction_code = quote(app('data')->frm['transaction_code']);
          $referer_url = Cookie::get('came_from');
          $confirm_string = gen_confirm_code(10);
          if (app('data')->settings['use_opt_in'] != 1) {
              $confirm_string = '';
          }

          $ip = app('data')->env['REMOTE_ADDR'];

          $question = app('data')->frm['question'];
          $answer = app('data')->frm['answer'];
          $q = 'insert into users set
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
          if (0 < app('data')->settings['startup_bonus']) {
              $q = 'insert into history set
		user_id = '.$last_id.',
		ec = '.app('data')->settings['startup_bonus_ec'].',
		amount = '.app('data')->settings['startup_bonus'].',
		actual_amount = '.app('data')->settings['startup_bonus'].',
		type=\'bonus\',
		date = now(),
		description = \'Startup bonus\'';
              db_query($q);
          }

          if (0 < $ref) {
              $q = 'select * from referal_stats where date = current_date() and user_id = '.$ref_id;
              $sth = db_query($q);
              $f = 0;
              while ($row = mysql_fetch_array($sth)) {
                  $f = 1;
              }

              if ($f == 0) {
                  $q = 'insert into referal_stats set date = current_date(), user_id = '.$ref_id.', income = 0, reg = 1';
                  $sth = db_query($q);
              } else {
                  $q = 'update referal_stats set reg = reg+1 where date = current_date() and user_id = '.$ref_id;
                  $sth = db_query($q);
              }
          }

          if (app('data')->settings['use_opt_in'] == 1) {
              $info = [];
              $info['username'] = app('data')->frm['username'];
              $info['confirm_string'] = $confirm_string;
              $info['name'] = app('data')->frm['fullname'];
              $info['ip'] = app('data')->env['REMOTE_ADDR'];
              send_template_mail('confirm_registration', app('data')->frm['email'], app('data')->settings['opt_in_email'], $info);
              throw new RedirectException('/?a=signup&say=confirm');
          } else {
              $q = 'select * from users where id = \''.$ref_id.'\'';
              $sth = db_query($q);
              while ($refinfo = mysql_fetch_array($sth)) {
                  $info = [];
                  $info['username'] = $refinfo['username'];
                  $info['name'] = $refinfo['name'];
                  $info['ref_username'] = app('data')->frm['username'];
                  $info['ref_name'] = app('data')->frm['fullname'];
                  $info['ref_email'] = app('data')->frm['email'];
                  send_template_mail('direct_signup_notification', $refinfo['email'], app('data')->settings['opt_in_email'], $info);
              }

              $info = [];
              $info['username'] = app('data')->frm['username'];
              $info['password'] = $password;
              $info['name'] = app('data')->frm['fullname'];
              $info['ip'] = app('data')->env['REMOTE_ADDR'];
              send_template_mail('registration', app('data')->frm['email'], app('data')->settings['opt_in_email'], $info);
              throw new RedirectException('/?a=signup&say=done');
          }

          throw new EmptyException();
      }
  }

  include app_path('Hm').'/inc/countries.inc';
  $ref = Cookie::get('referer');
  $q = 'select * from users where username = \''.$ref.'\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      view_assign('referer', $row);
  }

  view_assign('token', $token);
  view_assign('errors', $errors);
  view_assign('frm', app('data')->frm);
  view_assign('countries', $countries);
  view_assign('deny_registration', app('data')->settings['deny_registration']);
  view_execute('signup.blade.php');
