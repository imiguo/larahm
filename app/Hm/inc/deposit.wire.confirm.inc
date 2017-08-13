<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$ok = 1;
  $amount = sprintf('%0.2f', $frm['amount']);
  $h_id = sprintf('%d', $frm['h_id']);
  $q = 'select * from hm2_types where id = '.$h_id.' and closed = 0';
  $sth = db_query($q);
  $type = mysql_fetch_array($sth);
  if (!$type) {
      $smarty->assign('wrong_plan', 1);
  } else {
      $plan_name = $type['name'];
      $smarty->assign('plan_name', $plan_name);
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

              $smarty->assign('compound_percents', $compound_percents);
          } else {
              $smarty->assign('compound_min_percents', $type['compound_min_percent']);
              $smarty->assign('compound_max_percents', $type['compound_max_percent']);
          }
      }
  }

  $smarty->assign('use_compound', $use_compound);
  $q = 'select `value` from hm2_settings where name=\'wire_text\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $wire_txt = $row['value'];
  }

  $q = 'select count(*) as col, min(min_deposit) as min from hm2_plans where parent = '.$h_id;
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  if ($row) {
      if ($row['col'] == 0) {
          $smarty->assign('wrong_plan', 1);
          $ok = 0;
      }

      if ($amount < $row['min']) {
          $smarty->assign('less_than_min', 1);
          $smarty->assign('min_amount', number_format($row['min'], 2));
          $ok = 0;
      }
  } else {
      $smarty->assign('wrong_plan', 1);
      $ok = 0;
  }

  $accounting = get_user_balance($userinfo['id']);
  $max_deposit = 0;
  $q = 'select min(max_deposit) as min, max(max_deposit) as max from hm2_plans where parent = '.$h_id;
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
      $smarty->assign('max_deposit_less', 1);
      $smarty->assign('max_deposit_format', number_format($max_deposit, 2));
      $ok = 0;
  }

  if (($ok == 1 and $frm['action'] == 'confirm')) {
      $compound = sprintf('%.02f', $frm['compound']);
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

      $q = 'insert into hm2_wires set
	user_id = \''.$userinfo['id'].'\',
	pname = \''.quote($frm['pname']).'\',
	paddress = \''.quote($frm['paddress']).'\',
	pzip = \''.quote($frm['pcity']).'\',
	pcity = \''.quote($frm['pcity']).'\',
	pstate = \''.quote($frm['pstate']).'\',
	pcountry = \''.quote($frm['pcountry']).'\',
	bname = \''.quote($frm['bname']).'\',
	baddress = \''.quote($frm['baddress']).'\',
	bzip = \''.quote($frm['bzip']).'\',
	bcity = \''.quote($frm['bcity']).'\',
	bstate = \''.quote($frm['bstate']).'\',
	bcountry = \''.quote($frm['bcountry']).'\',
	baccount = \''.quote($frm['baccount']).'\',
	biban = \''.quote($frm['biban']).'\',
	bswift = \''.quote($frm['bswift']).(''.'\',
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
      $info['bank_name'] = $frm['bname'];
      $info['bank_account'] = $frm['baccount'];
      $info['plan'] = $plan_name;
      $q = 'select date_format(now() + interval '.$settings['time_dif'].' hour, \'%b-%e-%Y %r\') as date';
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      $info['date'] = $row['date'];
      $q = 'select email from hm2_users where id = 1';
      $sth = db_query($q);
      $admin_row = mysql_fetch_array($sth);
      send_template_mail('wire_admin_notification', $admin_row['email'], $settings['opt_in_email'], $info);
      send_template_mail('wire_user_notification', $userinfo['email'], $settings['opt_in_email'], $info);
      header('Location: ?a=deposit&say=deposit_wire_saved');
  }

  $compound = sprintf('%d', $frm['compound']);
  $smarty->assign('ok', $ok);
  $smarty->assign('h_id', $h_id);
  $smarty->assign('amount', number_format($amount, 2));
  $smarty->assign('famount', $amount);
  $smarty->assign('wire_txt', $wire_txt);
  $smarty->assign('compounding', $compound);
  $smarty->display('deposit.wire.confirm.tpl');
