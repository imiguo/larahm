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

$ok = 1;
  $amount = sprintf('%0.2f', app('data')->frm['amount']);
  $h_id = sprintf('%d', app('data')->frm['h_id']);
  $q = 'select * from types where id = '.$h_id.' and closed = 0';
  $sth = db_query($q);
  $type = mysql_fetch_array($sth);
  if (!$type) {
      view_assign('wrong_plan', 1);
  } else {
      $plan_name = $type['name'];
      view_assign('plan_name', $plan_name);
  }

  $use_compound = 0;
  if ($type['use_compound']) {
      if ($type['compound_max_deposit'] == 0) {
          $type['compound_max_deposit'] = $amount + 1;
      }

      if (($type['compound_min_deposit'] <= $amount and $amount <= $type['compound_max_deposit'])) {
          $use_compound = 1;
          if ($type['compound_percents_type'] == 1) {
              $cps = preg_split('/\\s*,\\s*/', $type['compound_percents']);
              $cps1 = [];
              foreach ($cps as $cp) {
                  array_push($cps1, sprintf('%d', $cp));
              }

              sort($cps1);
              $compound_percents = [];
              foreach ($cps1 as $cp) {
                  array_push($compound_percents, ['percent' => sprintf('%d', $cp)]);
              }

              view_assign('compound_percents', $compound_percents);
          } else {
              view_assign('compound_min_percents', $type['compound_min_percent']);
              view_assign('compound_max_percents', $type['compound_max_percent']);
          }
      }
  }

  view_assign('use_compound', $use_compound);
  $q = 'select `value` from settings where name=\'wire_text\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $wire_txt = $row['value'];
  }

  $q = 'select count(*) as col, min(min_deposit) as min from plans where parent = '.$h_id;
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  if ($row) {
      if ($row['col'] == 0) {
          view_assign('wrong_plan', 1);
          $ok = 0;
      }

      if ($amount < $row['min']) {
          view_assign('less_than_min', 1);
          view_assign('min_amount', number_format($row['min'], 2));
          $ok = 0;
      }
  } else {
      view_assign('wrong_plan', 1);
      $ok = 0;
  }

  $accounting = get_user_balance($userinfo['id']);
  $max_deposit = 0;
  $q = 'select min(max_deposit) as min, max(max_deposit) as max from plans where parent = '.$h_id;
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  if ($row) {
      if (0 < $row['min']) {
          if ($row['max'] < $accounting['total']) {
              $max_deposit = $row['max'];
          }
      }
  }

  if (($max_deposit < $amount and 0 < $max_deposit)) {
      view_assign('max_deposit_less', 1);
      view_assign('max_deposit_format', number_format($max_deposit, 2));
      $ok = 0;
  }

  if (($ok == 1 and app('data')->frm['action'] == 'confirm')) {
      $compound = sprintf('%.02f', app('data')->frm['compound']);
      if ($use_compound) {
          if ($type['compound_percents_type'] == 1) {
              $cps = preg_split('/\\s*,\\s*/', $type['compound_percents']);
              if (!in_array($compound, $cps)) {
                  $compound = $cps[0];
              }
          } else {
              if ($compound < $type['compound_min_percent']) {
                  $compound = $type['compound_min_percent'];
              }

              if ($type['compound_max_percent'] < $compound) {
                  $compound = $type['compound_max_percent'];
              }
          }
      }

      $q = 'insert into wires set
	user_id = \''.$userinfo['id'].'\',
	pname = \''.quote(app('data')->frm['pname']).'\',
	paddress = \''.quote(app('data')->frm['paddress']).'\',
	pzip = \''.quote(app('data')->frm['pcity']).'\',
	pcity = \''.quote(app('data')->frm['pcity']).'\',
	pstate = \''.quote(app('data')->frm['pstate']).'\',
	pcountry = \''.quote(app('data')->frm['pcountry']).'\',
	bname = \''.quote(app('data')->frm['bname']).'\',
	baddress = \''.quote(app('data')->frm['baddress']).'\',
	bzip = \''.quote(app('data')->frm['bzip']).'\',
	bcity = \''.quote(app('data')->frm['bcity']).'\',
	bstate = \''.quote(app('data')->frm['bstate']).'\',
	bcountry = \''.quote(app('data')->frm['bcountry']).'\',
	baccount = \''.quote(app('data')->frm['baccount']).'\',
	biban = \''.quote(app('data')->frm['biban']).'\',
	bswift = \''.quote(app('data')->frm['bswift']).(''.'\',
	amount = '.$amount.',
	type_id = '.$h_id.',
	wire_date = now(),
	status = \'new\',
	compound = '.$compound);
      db_query($q);
      $info = [];
      $info['username'] = $userinfo['username'];
      $info['name'] = $userinfo['name'];
      $info['amount'] = $amount;
      $info['currency'] = 'Bank Wire';
      $info['bank_name'] = app('data')->frm['bname'];
      $info['bank_account'] = app('data')->frm['baccount'];
      $info['plan'] = $plan_name;
      $q = 'select date_format(now() + interval '.app('data')->settings['time_dif'].' hour, \'%b-%e-%Y %r\') as date';
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      $info['date'] = $row['date'];
      $q = 'select email from users where id = 1';
      $sth = db_query($q);
      $admin_row = mysql_fetch_array($sth);
      send_template_mail('wire_admin_notification', $admin_row['email'], app('data')->settings['opt_in_email'], $info);
      send_template_mail('wire_user_notification', $userinfo['email'], app('data')->settings['opt_in_email'], $info);
      throw new RedirectException('/?a=deposit&say=deposit_wire_saved');
  }

  $compound = sprintf('%d', app('data')->frm['compound']);
  view_assign('ok', $ok);
  view_assign('h_id', $h_id);
  view_assign('amount', number_format($amount, 2));
  view_assign('famount', $amount);
  view_assign('wire_txt', $wire_txt);
  view_assign('compounding', $compound);
  view_execute('deposit.wire.confirm.blade.php');
