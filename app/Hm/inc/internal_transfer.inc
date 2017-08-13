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
    $smarty->assign('fatal', 'completed');
    $smarty->display('internal_transfer.tpl');
    exit();
}

  if (!$settings['internal_transfer_enabled']) {
      $smarty->assign('fatal', 'forbidden');
      $smarty->display('internal_transfer.tpl');
      exit();
  }

  if (0 < $settings['forbid_withdraw_before_deposit']) {
      $q = 'select count(*) as cnt from hm2_deposits where user_id = '.$userinfo['id'];
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      if ($row['cnt'] < 1) {
          $smarty->assign('say', 'no_deposits');
      }
  }

  $ab = get_user_balance($userinfo['id']);
  $ab_formated = [];
  $ab['withdraw_pending'] = 0 - $ab['withdraw_pending'];
  reset($ab);
  while (list($kk, $vv) = each($ab)) {
      $vv = floor($vv * 100) / 100;
      $ab_formated[$kk] = number_format($vv, 2);
  }

  $smarty->assign('ab_formated', $ab_formated);
  $q = 'select sum(actual_amount) as sm, ec from hm2_history where user_id = '.$userinfo['id'].' group by ec';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $sm = floor($row['sm'] * 100) / 100;
      $exchange_systems[$row['ec']]['balance'] = number_format($sm, 2);
      $exchange_systems[$row['ec']]['actual_balance'] = $row['sm'];
  }

  $ps = [];
  reset($exchange_systems);
  foreach ($exchange_systems as $id => $data) {
      array_push($ps, array_merge(['id' => $id], $data));
  }

  $smarty->assign('ps', $ps);
  if ($settings['hold_only_first_days'] == 1) {
      $q = 'select
              sum(hm2_history.actual_amount) as am,
	      hm2_history.ec
            from
              hm2_history,
              hm2_deposits,
              hm2_types
            where
              hm2_history.user_id = '.$userinfo[id].' and
	      hm2_history.deposit_id = hm2_deposits.id and
              hm2_types.id = hm2_deposits.type_id and
              now() - interval hm2_types.hold day < hm2_history.date and
              hm2_deposits.deposit_date + interval hm2_types.hold day > now() and
	      (hm2_history.type=\'earning\' or
		(hm2_history.type=\'deposit\' and (hm2_history.description like \'Compou%\' or hm2_history.description like \'<b>Archived transactions</b>:<br>Compound%\')))
	    group by hm2_history.ec
          ';
  } else {
      $q = 'select
              sum(hm2_history.actual_amount) as am,
	      hm2_history.ec
            from
              hm2_history,
              hm2_deposits,
              hm2_types
            where
              hm2_history.user_id = '.$userinfo[id].' and
	      hm2_history.deposit_id = hm2_deposits.id and
              hm2_types.id = hm2_deposits.type_id and
              now() - interval hm2_types.hold day < hm2_history.date and
	      (hm2_history.type=\'earning\' or
		(hm2_history.type=\'deposit\' and (hm2_history.description like \'Compou%\' or hm2_history.description like \'<b>Archived transactions</b>:<br>Compound%\')))
	    group by hm2_history.ec
          ';
  }

  $sth = db_query($q);
  $deps = [];
  $deps[0] = 0;
  $hold = [];
  while ($row = mysql_fetch_array($sth)) {
      array_push($hold, ['ec' => $row[ec], 'amount' => number_format($row[am], 2)]);
  }

  $smarty->assign('hold', $hold);
  if ($frm['action'] == 'preview_transaction') {
      $amount = sprintf('%.02f', $frm['amount']);
      $ec = intval($frm['ec']);
      if ($amount <= 0) {
          $smarty->assign('say', 'too_small_amount');
          $smarty->display('internal_transfer.tpl');
          exit();
      }

      if ($settings['hold_only_first_days'] == 1) {
          $q = 'select hm2_deposits.id, hm2_types.hold from hm2_deposits, hm2_types where user_id = '.$userinfo[id].' and hm2_types.id = hm2_deposits.type_id and ec='.$ec.' and hm2_deposits.deposit_date + interval hm2_types.hold day > now()';
      } else {
          $q = 'select hm2_deposits.id, hm2_types.hold from hm2_deposits, hm2_types where user_id = '.$userinfo[id].' and hm2_types.id = hm2_deposits.type_id and ec='.$ec;
      }

      $sth = db_query($q);
      $deps = [];
      $deps[0] = 0;
      $on_hold = 0;
      while ($row = mysql_fetch_array($sth)) {
          $q = 'select sum(actual_amount) as amount from hm2_history where user_id = '.$userinfo['id'].(''.' and ec = '.$ec.' and
		deposit_id = '.$row[id].' and date > now() - interval '.$row[hold].' day and
			(type=\'earning\' or
		(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\')));');
          ($sth1 = db_query($q));
          while ($row1 = mysql_fetch_array($sth1)) {
              $on_hold += $row1[amount];
          }
      }

      if ($exchange_systems[$ec]['actual_balance'] < $amount) {
          $smarty->assign('say', 'too_big_amount');
          $smarty->display('internal_transfer.tpl');
          exit();
      }

      if ($exchange_systems[$ec]['actual_balance'] - $on_hold < $amount) {
          $smarty->assign('say', 'on_hold');
          $smarty->display('internal_transfer.tpl');
          exit();
      }

      $recipient = quote($frm['account']);
      $q = 'select * from hm2_users where username = \''.$recipient.'\' and status = \'on\'';
      $sth = db_query($q);
      $user = mysql_fetch_array($sth);
      if (!$user) {
          $smarty->assign('say', 'user_not_found');
          $smarty->display('internal_transfer.tpl');
          exit();
      }

      $smarty->assign('user', $user);
      $smarty->assign('amount', $amount);
      $smarty->assign('ec', $ec);
      $smarty->assign('ec_name', $exchange_systems[$ec]['name']);
      $smarty->assign('comment', $frm['comment']);
      $smarty->assign('preview', 1);
      $smarty->display('internal_transfer.tpl');
      exit();
  }

  if ($frm['action'] == 'make_transaction') {
      if ($settings['use_transaction_code']) {
          if ($frm['transaction_code'] != $userinfo['transaction_code']) {
              $smarty->assign('fatal', 'invalid_transaction_code');
              $smarty->display('internal_transfer.tpl');
              exit();
          }
      }

      $amount = sprintf('%.02f', $frm['amount']);
      $ec = intval($frm['ec']);
      if ($amount <= 0) {
          $smarty->assign('say', 'too_small_amount');
          $smarty->display('internal_transfer.tpl');
          exit();
      }

      if ($settings['hold_only_first_days'] == 1) {
          $q = 'select hm2_deposits.id, hm2_types.hold from hm2_deposits, hm2_types where user_id = '.$userinfo[id].' and hm2_types.id = hm2_deposits.type_id and ec='.$ec.' and hm2_deposits.deposit_date + interval hm2_types.hold day > now()';
      } else {
          $q = 'select hm2_deposits.id, hm2_types.hold from hm2_deposits, hm2_types where user_id = '.$userinfo[id].' and hm2_types.id = hm2_deposits.type_id and ec='.$ec;
      }

      $sth = db_query($q);
      $deps = [];
      $deps[0] = 0;
      $on_hold = 0;
      while ($row = mysql_fetch_array($sth)) {
          $q = 'select sum(actual_amount) as amount from hm2_history where user_id = '.$userinfo['id'].(''.' and ec = '.$ec.' and
		deposit_id = '.$row[id].' and date > now() - interval '.$row[hold].' day and
			(type=\'earning\' or
		(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\')));');
          ($sth1 = db_query($q));
          while ($row1 = mysql_fetch_array($sth1)) {
              $on_hold += $row1[amount];
          }
      }

      if ($exchange_systems[$ec]['actual_balance'] - $on_hold < $amount) {
          $smarty->assign('say', 'on_hold');
          $smarty->display('internal_transfer.tpl');
          exit();
      }

      if ($exchange_systems[$ec]['actual_balance'] < $amount) {
          $smarty->assign('say', 'too_big_amount');
          $smarty->display('internal_transfer.tpl');
          exit();
      }

      $recipient = quote($frm['account']);
      $q = 'select * from hm2_users where username = \''.$recipient.'\' and status = \'on\'';
      $sth = db_query($q);
      $user = mysql_fetch_array($sth);
      if (!$user) {
          $smarty->assign('say', 'user_not_found');
          $smarty->display('internal_transfer.tpl');
          exit();
      }

      $q = 'insert into hm2_history set
            user_id = '.$userinfo['id'].(''.',
            amount = -'.$amount.',
            actual_amount = -'.$amount.',
            type = \'internal_transaction_spend\',
            description = \'Internal transaction to `').$user['username'].'` account.'.($frm['comment'] ? ' Comments: '.$frm['comment'] : '').(''.'\',
            date = now(),
            ec = '.$ec.'
         ');
      db_query($q);
      $q = 'insert into hm2_history set
            user_id = '.$user['id'].(''.',
            amount = '.$amount.',
            actual_amount = '.$amount.',
            type = \'internal_transaction_receive\',
            description = \'Internal transaction from `').$userinfo['username'].'` account.'.($frm['comment'] ? ' Comments: '.$frm['comment'] : '').(''.'\',
            date = now(),
            ec = '.$ec.'
         ');
      db_query($q);
      header('Location: ?a=internal_transfer&complete=1');
      exit();
  }

  $smarty->display('internal_transfer.tpl');
