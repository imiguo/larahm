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
    $smarty->assign('fatal', 'update_completed');
    $smarty->display('change_compound.tpl');
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
      $smarty->display('change_compound.tpl');
      exit();
  }

  $q = 'select * from hm2_types where id = '.$deposit['type_id'];
  $sth = db_query($q);
  $type = mysql_fetch_array($sth);
  if (!$type['use_compound']) {
      $smarty->assign('fatal', 'compound_forbidden');
      $smarty->display('change_compound.tpl');
      exit();
  }

  $amount = $deposit['actual_amount'];
  if ($type['compound_max_deposit'] == 0) {
      $type['compound_max_deposit'] = $amount + 1;
  }

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

  if ($frm['action'] == 'change') {
      $c_percent = sprintf('%.02f', $frm['percent']);
      if ($c_percent < 0) {
          $c_percent = 0;
      }

      if (100 < $c_percent) {
          $c_percent = 100;
      }

      if (($type['compound_min_deposit'] <= $amount and $amount <= $type['compound_max_deposit'])) {
          if ($type['compound_percents_type'] == 1) {
              $cps = preg_split('/\\s*,\\s*/', $type['compound_percents']);
              if (!in_array($c_percent, $cps)) {
                  $c_percent = $cps[0];
              }
          } else {
              if ($c_percent < $type['compound_min_percent']) {
                  $c_percent = $type['compound_min_percent'];
              }

              if ($type['compound_max_percent'] < $c_percent) {
                  $c_percent = $type['compound_max_percent'];
              }
          }
      } else {
          $c_percent = 0;
      }

      $q = 'update hm2_deposits set compound = '.$c_percent.' where user_id = '.$user_id.' and id = '.$deposit_id;
      db_query($q);
      header('Location: ?a=change_compound&complete=1');
      exit();
  }

  $deposit['deposit'] = number_format($deposit['actual_amount'], 2);
  $deposit['compound'] = sprintf('%.02f', $deposit['compound']);
  $smarty->assign('deposit', $deposit);
  $smarty->assign('type', $type);
  $smarty->display('change_compound.tpl');
