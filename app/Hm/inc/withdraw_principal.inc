<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if ($frm['complete']) {
    $smarty->assign('fatal', 'withdraw_complete');
    $smarty->display('withdraw_principal.tpl');
    exit();
}

  $user_id = $userinfo['id'];
  $deposit_id = intval($frm['deposit']);
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
      $smarty->assign('fatal', 'deposit_not_found');
      $smarty->display('withdraw_principal.tpl');
      exit();
  }

  $q = 'select * from hm2_types where id = '.$deposit['type_id'];
  $sth = db_query($q);
  $type = mysql_fetch_array($sth);
  if (!$type['withdraw_principal']) {
      $smarty->assign('fatal', 'withdraw_forbidden');
      $smarty->display('withdraw_principal.tpl');
      exit();
  }

  if ($deposit['deposit_duration'] < $type['withdraw_principal_duration']) {
      $smarty->assign('fatal', 'too_early_withdraw');
      $smarty->display('withdraw_principal.tpl');
      exit();
  }

  if (($type['withdraw_principal_duration_max'] <= $deposit['deposit_duration'] and $type['withdraw_principal_duration_max'] != 0)) {
      $smarty->assign('fatal', 'too_late_withdraw');
      $smarty->display('withdraw_principal.tpl');
      exit();
  }

  $deposit['deposit'] = sprintf('%.02f', floor($deposit['actual_amount'] * 100) / 100);
  if ($frm['action'] == 'withdraw_preview') {
      $withdraw_amount = sprintf('%.02f', $frm['amount']);
      if ($deposit['actual_amount'] < $withdraw_amount) {
          $smarty->assign('deposit', $deposit);
          $smarty->assign('type', $type);
          $smarty->assign('say', 'too_big_amount');
          $smarty->display('withdraw_principal.tpl');
          exit();
      }

      if ($withdraw_amount <= 0) {
          $smarty->assign('deposit', $deposit);
          $smarty->assign('type', $type);
          $smarty->assign('say', 'too_small_amount');
          $smarty->display('withdraw_principal.tpl');
          exit();
      }

      $fee = floor($withdraw_amount * $type['withdraw_principal_percent']) / 100;
      if ($fee < 0.01) {
          $fee = 0.01;
      }

      $to_balance = $withdraw_amount - $fee;
      if ($to_balance < 0) {
          $to_balance = 0;
      }

      $smarty->assign('deposit', $deposit);
      $smarty->assign('type', $type);
      $smarty->assign('preview', 1);
      $smarty->assign('amount', $withdraw_amount);
      $smarty->assign('fee', $fee);
      $smarty->assign('to_balance', $to_balance);
      $smarty->display('withdraw_principal.tpl');
      exit();
  }

  if ($frm['action'] == 'withdraw') {
      $withdraw_amount = sprintf('%.02f', $frm['amount']);
      if ($deposit['actual_amount'] < $withdraw_amount) {
          $smarty->assign('deposit', $deposit);
          $smarty->assign('type', $type);
          $smarty->assign('say', 'too_big_amount');
          $smarty->display('withdraw_principal.tpl');
          exit();
      }

      if ($withdraw_amount <= 0) {
          $smarty->assign('deposit', $deposit);
          $smarty->assign('type', $type);
          $smarty->assign('say', 'too_small_amount');
          $smarty->display('withdraw_principal.tpl');
          exit();
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
               ec = '.$deposit['ec'].'
          ';
      db_query($q);
      header('Location: ?a=withdraw_principal&complete=1&deposit='.$deposit['id']);
      exit();
  }

  $smarty->assign('deposit', $deposit);
  $smarty->assign('type', $type);
  $smarty->display('withdraw_principal.tpl');
