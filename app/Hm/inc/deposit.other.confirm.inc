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
  $ps = substr($frm['type'], 8);
  $accounting = get_user_balance($userinfo['id']);
  if (($settings[use_add_funds] and $h_id == -1)) {
      if ($amount < 0.01) {
          $smarty->assign('zero_amount', 1);
          $ok = 0;
      }

      $plan_name = 'Deposit to Account';
  } else {
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
  }

  $q = 'select * from hm2_processings where id = '.$ps;
  $sth = db_query($q);
  $processing = mysql_fetch_array($sth);
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

      $infofields = $frm['fields'];
      $fields = serialize($infofields);
      if ($h_id == -1) {
          $h_id = 0;
      }

      $q = 'insert into hm2_pending_deposits set
            user_id = \''.$userinfo['id'].'\',
            `fields` = \''.quote($fields).(''.'\',
            ec = '.$ps.',
            amount = '.$amount.',
            type_id = '.$h_id.',
            date = now(),
            status = \'new\',
            compound = '.$compound);
      db_query($q);
      $fields = '';
      $infofields = unserialize($processing['infofields']);
      foreach ($infofields as $id => $name) {
          $fields .= $name.': '.$frm['fields'][$id].'';
      }

      $info = [];
      $info['username'] = $userinfo['username'];
      $info['name'] = $userinfo['name'];
      $info['amount'] = number_format($amount, 2);
      $info['currency'] = $exchange_systems[$ps]['name'];
      $info['fields'] = $fields;
      $info['plan'] = $plan_name;
      $info['compound'] = $compound;
      $q = 'select date_format(now() + interval '.$settings['time_dif'].' hour, \'%b-%e-%Y %r\') as date';
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      $info['date'] = $row['date'];
      $q = 'select email from hm2_users where id = 1';
      $sth = db_query($q);
      $admin_row = mysql_fetch_array($sth);
      send_template_mail('pending_deposit_admin_notification', $admin_row['email'], $settings['opt_in_email'], $info);
      if ($h_id == 0) {
          header('Location: ?a=add_funds&say=deposit_saved');
      } else {
          header('Location: ?a=deposit&say=deposit_saved');
      }
  }

  $compound = sprintf('%d', $frm['compound']);
  $processing['description'] = preg_replace('/#amount#/', number_format($amount, 2), $processing['description']);
  $processing['description'] = preg_replace('/#username#/', $userinfo['username'], $processing['description']);
  $processing['description'] = preg_replace('/#id#/', $userinfo['id'], $processing['description']);
  $processing['description'] = preg_replace('/#name#/', $userinfo['name'], $processing['description']);
  $smarty->assign('description', $processing['description']);
  $infofields = unserialize($processing['infofields']);
  $fields = [];
  foreach ($infofields as $id => $name) {
      array_push($fields, ['id' => $id, 'name' => $name]);
  }

  $smarty->assign('fields', $fields);
  $smarty->assign('ok', $ok);
  $smarty->assign('h_id', $h_id);
  $smarty->assign('amount', number_format($amount, 2));
  $smarty->assign('famount', $amount);
  $smarty->assign('compounding', $compound);
  $smarty->assign('type', $frm['type']);
  $smarty->assign('cname', $exchange_systems[$ps]['name']);
  $smarty->display('deposit.other.confirm.tpl');
