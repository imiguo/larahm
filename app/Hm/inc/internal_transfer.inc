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
    view_assign('fatal', 'completed');
    view_execute('internal_transfer.tpl');
    throw new EmptyException();
}

  if (!app('data')->settings['internal_transfer_enabled']) {
      view_assign('fatal', 'forbidden');
      view_execute('internal_transfer.tpl');
      throw new EmptyException();
  }

  if (0 < app('data')->settings['forbid_withdraw_before_deposit']) {
      $q = 'select count(*) as cnt from hm2_deposits where user_id = '.$userinfo['id'];
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      if ($row['cnt'] < 1) {
          view_assign('say', 'no_deposits');
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

  view_assign('ab_formated', $ab_formated);
  $q = 'select sum(actual_amount) as sm, ec from hm2_history where user_id = '.$userinfo['id'].' group by ec';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $sm = floor($row['sm'] * 100) / 100;
      app('data')->exchange_systems[$row['ec']]['balance'] = number_format($sm, 2);
      app('data')->exchange_systems[$row['ec']]['actual_balance'] = $row['sm'];
  }

  $ps = [];
  reset(app('data')->exchange_systems);
  foreach (app('data')->exchange_systems as $id => $data) {
      array_push($ps, array_merge(['id' => $id], $data));
  }

  view_assign('ps', $ps);
  if (app('data')->settings['hold_only_first_days'] == 1) {
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

  view_assign('hold', $hold);
  if (app('data')->frm['action'] == 'preview_transaction') {
      $amount = sprintf('%.02f', app('data')->frm['amount']);
      $ec = intval(app('data')->frm['ec']);
      if ($amount <= 0) {
          view_assign('say', 'too_small_amount');
          view_execute('internal_transfer.tpl');
          throw new EmptyException();
      }

      if (app('data')->settings['hold_only_first_days'] == 1) {
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

      if (app('data')->exchange_systems[$ec]['actual_balance'] < $amount) {
          view_assign('say', 'too_big_amount');
          view_execute('internal_transfer.tpl');
          throw new EmptyException();
      }

      if (app('data')->exchange_systems[$ec]['actual_balance'] - $on_hold < $amount) {
          view_assign('say', 'on_hold');
          view_execute('internal_transfer.tpl');
          throw new EmptyException();
      }

      $recipient = quote(app('data')->frm['account']);
      $q = 'select * from hm2_users where username = \''.$recipient.'\' and status = \'on\'';
      $sth = db_query($q);
      $user = mysql_fetch_array($sth);
      if (!$user) {
          view_assign('say', 'user_not_found');
          view_execute('internal_transfer.tpl');
          throw new EmptyException();
      }

      view_assign('user', $user);
      view_assign('amount', $amount);
      view_assign('ec', $ec);
      view_assign('ec_name', app('data')->exchange_systems[$ec]['name']);
      view_assign('comment', app('data')->frm['comment']);
      view_assign('preview', 1);
      view_execute('internal_transfer.tpl');
      throw new EmptyException();
  }

  if (app('data')->frm['action'] == 'make_transaction') {
      if (app('data')->settings['use_transaction_code']) {
          if (app('data')->frm['transaction_code'] != $userinfo['transaction_code']) {
              view_assign('fatal', 'invalid_transaction_code');
              view_execute('internal_transfer.tpl');
              throw new EmptyException();
          }
      }

      $amount = sprintf('%.02f', app('data')->frm['amount']);
      $ec = intval(app('data')->frm['ec']);
      if ($amount <= 0) {
          view_assign('say', 'too_small_amount');
          view_execute('internal_transfer.tpl');
          throw new EmptyException();
      }

      if (app('data')->settings['hold_only_first_days'] == 1) {
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

      if (app('data')->exchange_systems[$ec]['actual_balance'] - $on_hold < $amount) {
          view_assign('say', 'on_hold');
          view_execute('internal_transfer.tpl');
          throw new EmptyException();
      }

      if (app('data')->exchange_systems[$ec]['actual_balance'] < $amount) {
          view_assign('say', 'too_big_amount');
          view_execute('internal_transfer.tpl');
          throw new EmptyException();
      }

      $recipient = quote(app('data')->frm['account']);
      $q = 'select * from hm2_users where username = \''.$recipient.'\' and status = \'on\'';
      $sth = db_query($q);
      $user = mysql_fetch_array($sth);
      if (!$user) {
          view_assign('say', 'user_not_found');
          view_execute('internal_transfer.tpl');
          throw new EmptyException();
      }

      $q = 'insert into hm2_history set
            user_id = '.$userinfo['id'].(''.',
            amount = -'.$amount.',
            actual_amount = -'.$amount.',
            type = \'internal_transaction_spend\',
            description = \'Internal transaction to `').$user['username'].'` account.'.(app('data')->frm['comment'] ? ' Comments: '.app('data')->frm['comment'] : '').(''.'\',
            date = now(),
            ec = '.$ec.'
         ');
      db_query($q);
      $q = 'insert into hm2_history set
            user_id = '.$user['id'].(''.',
            amount = '.$amount.',
            actual_amount = '.$amount.',
            type = \'internal_transaction_receive\',
            description = \'Internal transaction from `').$userinfo['username'].'` account.'.(app('data')->frm['comment'] ? ' Comments: '.app('data')->frm['comment'] : '').(''.'\',
            date = now(),
            ec = '.$ec.'
         ');
      db_query($q);
      throw new RedirectException('/?a=internal_transfer&complete=1');
  }

  view_execute('internal_transfer.tpl');
