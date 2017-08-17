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

if (app('data')->frm['complete']) {
    view_assign('fatal', 'withdraw_complete');
    view_execute('withdraw_principal.tpl');
    throw new EmptyException();
}

  $user_id = $userinfo['id'];
  $deposit_id = intval(app('data')->frm['deposit']);
  $q = 'select
               *,
               (to_days(now()) - to_days(deposit_date)) as deposit_duration
         from
               hm2_deposits
         where
               user_id = '.$user_id.' and
               id = '.$deposit_id.'
        ';
  $sth = db_query($q);
  $deposit = mysql_fetch_array($sth);
  if (!$deposit) {
      view_assign('fatal', 'deposit_not_found');
      view_execute('withdraw_principal.tpl');
      throw new EmptyException();
  }

  $q = 'select * from hm2_types where id = '.$deposit['type_id'];
  $sth = db_query($q);
  $type = mysql_fetch_array($sth);
  if (!$type['withdraw_principal']) {
      view_assign('fatal', 'withdraw_forbidden');
      view_execute('withdraw_principal.tpl');
      throw new EmptyException();
  }

  if ($deposit['deposit_duration'] < $type['withdraw_principal_duration']) {
      view_assign('fatal', 'too_early_withdraw');
      view_execute('withdraw_principal.tpl');
      throw new EmptyException();
  }

  if (($type['withdraw_principal_duration_max'] <= $deposit['deposit_duration'] and $type['withdraw_principal_duration_max'] != 0)) {
      view_assign('fatal', 'too_late_withdraw');
      view_execute('withdraw_principal.tpl');
      throw new EmptyException();
  }

  $deposit['deposit'] = sprintf('%.02f', floor($deposit['actual_amount'] * 100) / 100);
  if (app('data')->frm['action'] == 'withdraw_preview') {
      $withdraw_amount = sprintf('%.02f', app('data')->frm['amount']);
      if ($deposit['actual_amount'] < $withdraw_amount) {
          view_assign('deposit', $deposit);
          view_assign('type', $type);
          view_assign('say', 'too_big_amount');
          view_execute('withdraw_principal.tpl');
          throw new EmptyException();
      }

      if ($withdraw_amount <= 0) {
          view_assign('deposit', $deposit);
          view_assign('type', $type);
          view_assign('say', 'too_small_amount');
          view_execute('withdraw_principal.tpl');
          throw new EmptyException();
      }

      $fee = floor($withdraw_amount * $type['withdraw_principal_percent']) / 100;
      if ($fee < 0.01) {
          $fee = 0.01;
      }

      $to_balance = $withdraw_amount - $fee;
      if ($to_balance < 0) {
          $to_balance = 0;
      }

      view_assign('deposit', $deposit);
      view_assign('type', $type);
      view_assign('preview', 1);
      view_assign('amount', $withdraw_amount);
      view_assign('fee', $fee);
      view_assign('to_balance', $to_balance);
      view_execute('withdraw_principal.tpl');
      throw new EmptyException();
  }

  if (app('data')->frm['action'] == 'withdraw') {
      $withdraw_amount = sprintf('%.02f', app('data')->frm['amount']);
      if ($deposit['actual_amount'] < $withdraw_amount) {
          view_assign('deposit', $deposit);
          view_assign('type', $type);
          view_assign('say', 'too_big_amount');
          view_execute('withdraw_principal.tpl');
          throw new EmptyException();
      }

      if ($withdraw_amount <= 0) {
          view_assign('deposit', $deposit);
          view_assign('type', $type);
          view_assign('say', 'too_small_amount');
          view_execute('withdraw_principal.tpl');
          throw new EmptyException();
      }

      $fee = floor($withdraw_amount * $type['withdraw_principal_percent']) / 100;
      if ($fee < 0.01) {
          $fee = 0.01;
      }

      $to_balance = $withdraw_amount - $fee;
      if ($to_balance < 0) {
          $to_balance = 0;
      }

      $actual_amount = sprintf('%.02f', $deposit['actual_amount']);
      if ($actual_amount <= $withdraw_amount) {
          $q = 'update hm2_deposits set actual_amount = 0, amount = 0, status = \'off\' where user_id = '.$user_id.' and id = '.$deposit_id;
      } else {
          $q = 'update hm2_deposits set actual_amount = actual_amount - '.$withdraw_amount.', amount = amount - '.$withdraw_amount.' where user_id = '.$user_id.' and id = '.$deposit_id;
      }

      db_query($q);
      $q = 'insert into hm2_history set
               user_id = '.$user_id.',
               amount = '.$to_balance.',
               actual_amount = '.$to_balance.',
               type = \'early_deposit_release\',
               description = \'Pincipal withdraw '.$withdraw_amount.' from $'.$deposit['deposit'].' deposit from the '.quote($type['name']).'\',
               date = now(),
               ec = '.$deposit['ec'];
      db_query($q);
      throw new RedirectException('/?a=withdraw_principal&complete=1&deposit='.$deposit['id']);
  }

  view_assign('deposit', $deposit);
  view_assign('type', $type);
  view_execute('withdraw_principal.tpl');
