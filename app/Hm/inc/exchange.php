<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Exceptions\EmptyException;
use App\Exceptions\RedirectException;

$id = $userinfo['id'];
  if (app('data')->frm['display'] == 'ok') {
      view_assign('exchanged', 1);
      view_execute('exchange_preview.blade.php');
      throw new EmptyException();
  }

  if (app('data')->frm['action'] == 'preview') {
      $from = intval(app('data')->frm['from']);
      $q = 'select sum(actual_amount) as sm from history where user_id = '.$id.' and ec = '.$from;
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      $row['sm'] = floor($row['sm'] * 100) / 100;
      if ((! isset(app('data')->exchange_systems[$from]) or (app('data')->exchange_systems[$from]['status'] != 1 and $row['sm'] <= 0))) {
          view_assign('error', 'no_from');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      $to = intval(app('data')->frm[''.'to_'.$from]);
      if ((((app('data')->frm['to'] === '' or ! isset(app('data')->exchange_systems[$to])) or app('data')->exchange_systems[$to]['status'] != 1) or $to == $from)) {
          view_assign('error', 'no_to');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      app('data')->frm[''.'amount_'.$from] = str_replace(',', '.', app('data')->frm[''.'amount_'.$from]);
      $amount = sprintf('%.02f', app('data')->frm[''.'amount_'.$from]);
      if (app('data')->settings['hold_only_first_days'] == 1) {
          $q = 'select deposits.id, types.hold from deposits, types where user_id = '.$userinfo[id].' and types.id = deposits.type_id and deposits.deposit_date + interval types.hold day > now()';
      } else {
          $q = 'select deposits.id, types.hold from deposits, types where user_id = '.$userinfo[id].' and types.id = deposits.type_id ';
      }

    ($sthz = db_query($q));
      $deps = [];
      $deps[0] = 0;
      $on_hold = 0;
      while ($rowz = mysql_fetch_array($sthz)) {
          $q = 'select sum(actual_amount) as amount from history where user_id = '.$userinfo['id'].(''.' and ec = '.$from.' and
		deposit_id = '.$rowz[id].' and date > now() - interval '.$rowz[hold].' day and
			(type=\'earning\' or
		(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\')));');
          ($sth1 = db_query($q));
          while ($row1 = mysql_fetch_array($sth1)) {
              $on_hold += $row1[amount];
          }
      }

      if ($amount <= 0) {
          view_assign('error', 'no_amount');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      if ($row['sm'] < $amount) {
          view_assign('error', 'to_big_amount');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      if ($row['sm'] - $on_hold < $amount) {
          view_assign('error', 'on_hold');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      $q = 'select * from exchange_rates where sfrom = '.$from.' and sto = '.$to;
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      $percent = $row['percent'];
      $percent /= 100;
      if (1 <= $percent) {
          view_assign('error', 'exchange_forbidden');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      $exchange_amount = sprintf('%.02f', (1 - $percent) * $amount);
      if ($exchange_amount <= 0) {
          view_assign('error', 'to_small_amount');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      view_assign('from', $from);
      view_assign('from_name', app('data')->exchange_systems[$from]['name']);
      view_assign('to', $to);
      view_assign('to_name', app('data')->exchange_systems[$to]['name']);
      view_assign('amount', $amount);
      view_assign('exchange_amount', $exchange_amount);
      view_execute('exchange_preview.blade.php');
      throw new EmptyException();
  }

  if (app('data')->frm['action'] == 'exchange') {
      $from = intval(app('data')->frm['from']);
      $q = 'select sum(actual_amount) as sm from history where user_id = '.$id.' and ec = '.$from;
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      $row['sm'] = floor($row['sm'] * 100) / 100;
      if ((! isset(app('data')->exchange_systems[$from]) or (app('data')->exchange_systems[$from]['status'] != 1 and $row['sm'] <= 0))) {
          view_assign('error', 'no_from');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      $to = intval(app('data')->frm['to']);
      if (((app('data')->frm['to'] == '' or ! isset(app('data')->exchange_systems[$to])) or app('data')->exchange_systems[$to]['status'] != 1)) {
          view_assign('error', 'no_to');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      $amount = sprintf('%.02f', app('data')->frm['amount']);
      if (app('data')->settings['hold_only_first_days'] == 1) {
          $q = 'select deposits.id, types.hold from deposits, types where user_id = '.$userinfo[id].' and types.id = deposits.type_id and deposits.deposit_date + interval types.hold day > now()';
      } else {
          $q = 'select deposits.id, types.hold from deposits, types where user_id = '.$userinfo[id].' and types.id = deposits.type_id ';
      }

    ($sthz = db_query($q));
      $deps = [];
      $deps[0] = 0;
      $on_hold = 0;
      while ($rowz = mysql_fetch_array($sthz)) {
          $q = 'select sum(actual_amount) as amount from history where user_id = '.$userinfo['id'].(''.' and ec = '.$from.' and
		deposit_id = '.$rowz[id].' and date > now() - interval '.$rowz[hold].' day and
			(type=\'earning\' or
		(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\')));');
          ($sth1 = db_query($q));
          while ($row1 = mysql_fetch_array($sth1)) {
              $on_hold += $row1[amount];
          }
      }

      if ($amount <= 0) {
          view_assign('error', 'no_amount');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      if ($row['sm'] < $amount) {
          view_assign('error', 'to_big_amount');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      if ($row['sm'] - $on_hold < $amount) {
          view_assign('error', 'on_hold');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      $q = 'select * from exchange_rates where sfrom = '.$from.' and sto = '.$to;
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      $percent = $row['percent'];
      $percent /= 100;
      if (1 <= $percent) {
          view_assign('error', 'exchange_forbidden');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      $exchange_amount = sprintf('%.02f', (1 - $percent) * $amount);
      if ($exchange_amount <= 0) {
          view_assign('error', 'to_small_amount');
          view_execute('exchange_preview.blade.php');
          throw new EmptyException();
      }

      $from_name = app('data')->exchange_systems[$from]['name'];
      $to_name = app('data')->exchange_systems[$to]['name'];
      $q = 'insert into history set
             user_id = '.$id.',
             amount = -'.$amount.',
             actual_amount = -'.$amount.',
             date = now(),
             type = \'exchange_in\',
             description = \'Send $'.$amount.' '.$from_name.' to '.$to_name.'\',
             ec = '.$from.'
          ';
      db_query($q);
      $q = 'insert into history set
             user_id = '.$id.',
             amount = '.$exchange_amount.',
             actual_amount = '.$exchange_amount.',
             date = now(),
             type = \'exchange_out\',
             description = \'Receive $'.$exchange_amount.' '.$to_name.' from '.$from_name.'\',
             ec = '.$to.'
          ';
      db_query($q);
      $info = [];
      $info['username'] = $userinfo['username'];
      $info['name'] = $userinfo['name'];
      $info['currency_from'] = app('data')->exchange_systems[$from]['name'];
      $info['amount_from'] = number_format($amount, 2);
      $info['currency_to'] = app('data')->exchange_systems[$to]['name'];
      $info['amount_to'] = number_format($exchange_amount, 2);
      $q = 'select email from users where id = 1';
      $sth = db_query($q);
      $admin_email = '';
      while ($row = mysql_fetch_array($sth)) {
          $admin_email = $row['email'];
      }

      send_template_mail('exchange_admin_notification', $admin_email, app('data')->settings['system_email'], $info);
      send_template_mail('exchange_user_notification', $userinfo[email], app('data')->settings['system_email'], $info);
      throw new RedirectException('/?a=exchange&display=ok');
  }

  $balances = [];
  $tmp_amounts = [];
  $q = 'select sum(actual_amount) as sm, ec from history where user_id = '.$id.' group by ec order by ec asc';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $row['sm'] = floor($row['sm'] * 100) / 100;
      if (sprintf('%.02f', $row['sm']) <= 0) {
          continue;
      }

      $tmp_amounts[$row['ec']] = $row['sm'];
      $tos = [];
      foreach (app('data')->exchange_systems as $to => $data) {
          $q1 = 'select * from exchange_rates where sfrom = '.$row['ec'].' and sto = '.$to;
          $sth1 = db_query($q1);
          $row1 = mysql_fetch_array($sth1);
          $row1['percent'] = intval($row1['percent']);
          if (($row1['percent'] != 100 and app('data')->exchange_systems[$to]['status'])) {
              array_push($tos, ['to' => $to, 'ec_name' => app('data')->exchange_systems[$to]['name'], 'percent' => $row1['percent']]);
              continue;
          }
      }

      if (app('data')->settings['hold_only_first_days'] == 1) {
          $q = 'select deposits.id, types.hold from deposits, types where user_id = '.$userinfo[id].' and types.id = deposits.type_id and deposits.deposit_date + interval types.hold day > now()';
      } else {
          $q = 'select deposits.id, types.hold from deposits, types where user_id = '.$userinfo[id].' and types.id = deposits.type_id ';
      }

    ($sthz = db_query($q));
      $deps = [];
      $deps[0] = 0;
      $on_hold = 0;
      while ($rowz = mysql_fetch_array($sthz)) {
          $q = 'select sum(actual_amount) as amount from history where user_id = '.$userinfo['id'].(''.' and ec = '.$row[ec].' and
		deposit_id = '.$rowz[id].' and date > now() - interval '.$rowz[hold].' day and
			(type=\'earning\' or
		(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\')));');
          ($sth1 = db_query($q));
          while ($row1 = mysql_fetch_array($sth1)) {
              $on_hold += $row1[amount];
          }
      }

      array_push($balances, ['balance' => sprintf('%.02f', $row['sm'] - $on_hold), 'ec' => $row['ec'], 'ec_name' => app('data')->exchange_systems[$row['ec']]['name'], 'tos' => $tos]);
  }

  view_assign('ec', $balances);
  $exch = [];
  $q = 'select * from exchange_rates';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $exch[$row['sfrom']][$row['sto']] = $row['percent'];
  }

  $exchange = [];
  foreach (app('data')->exchange_systems as $from => $data) {
      if ((! $data['status'] and sprintf('%.02f', $tmp_amounts[$from]) <= 0)) {
          continue;
      }

      $tos = [];
      foreach (app('data')->exchange_systems as $to => $data) {
          if ((! $data['status'] and sprintf('%.02f', $tmp_amounts[$to]) <= 0)) {
              continue;
          }

          if (! $data['status']) {
              $exch[$from][$to] = 100;
          }

          if ($from == $to) {
              $exch[$from][$to] = 100;
          }

          array_push($tos, ['to' => $to, 'percent' => sprintf('%.02f', $exch[$from][$to])]);
      }

      array_push($exchange, ['from' => $from, 'tos' => $tos]);
  }

  view_assign('exchange', $exchange);
  view_execute('exchange.blade.php');
