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

  if (app('data')->frm['action'] == 'send') {
      $from = ($userinfo['logged'] ? $userinfo['email'] : app('data')->frm['email']);
      $message = app('data')->settings['site_name'].' Support request from '.date('l dS of F Y h:i:s A').'

---------------------------------------------------------------';
      $message .= app('data')->frm['message'];
      $message .= '

---------------------------------------------------------------';
      $message .= 'User Additional Info :';
      if ($userinfo['logged'] == 1) {
          $accounting = [];
          $accounting = get_user_balance($userinfo['id']);
          $message .= 'User      : '.$userinfo['username'].'';
          $message .= 'User Name : '.$userinfo['name'].'';
          $message .= 'E-Mail    : '.$userinfo['email'].'';
          $message .= 'E-Gold Acc : '.$userinfo['egold_account'].'';
          if (app('data')->settings[def_payee_account_intgold]) {
              $message .= 'IntGold Acc : '.$userinfo['intgold_account'].'';
          }

          if (app('data')->settings[def_payee_account_stormpay]) {
              $message .= 'StormPay Acc : '.$userinfo['stormpay_account'].'';
          }

          if (app('data')->settings[def_payee_account_ebullion]) {
              $message .= 'e-Bullion Acc : '.$userinfo['ebullion_account'].'';
          }

          if (app('data')->settings[def_payee_account_paypal]) {
              $message .= 'PayPal Acc : '.$userinfo['paypal_account'].'';
          }

          if (app('data')->settings[def_payee_account_goldmoney]) {
              $message .= 'GoldMoney Acc : '.$userinfo['goldmoney_account'].'';
          }

          if (app('data')->settings[def_payee_account_eeecurrency]) {
              $message .= 'eeeCurrency Acc : '.$userinfo['eeecurrency_account'].'';
          }

          if (app('data')->settings[def_payee_account_pecunix]) {
              $message .= 'Pecunix Acc : '.$userinfo['pecunix_account'].'';
          }

          $message .= 'Status    : '.$userinfo['status'].'';
          $message .= 'Active Deposits  : $'.sprintf('%.02f', $accounting['active_deposit']).'';
      } else {
          $message .= 'User Name : '.app('data')->frm['name'].'';
          $message .= 'E-Mail    : '.app('data')->frm['email'].'';
          $message .= 'Not Registered/Logged user';
      }

      $message .= 'IP Address: '.$_SERVER['REMOTE_ADDR'].'';
      $message .= 'Language  : '.$_SERVER['HTTP_ACCEPT_LANGUAGE'].'';
      $q = 'select * from users where id = 1';
      $sth = db_query($q);
      $admin_email = '';
      while ($row = mysql_fetch_array($sth)) {
          $admin_email = $row['email'];
      }

      send_mail($admin_email, app('data')->settings['site_name'].' Support Request', $message, 'From: '.$from);
      throw new RedirectException('/?a=support&say=send');
  }

  view_assign('token', $token);
  view_assign('say', app('data')->frm['say']);
  view_execute('support.blade.php');
