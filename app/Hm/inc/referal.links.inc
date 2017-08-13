<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$smarty->assign('site_name', $settings['site_name']);
  if ($settings[use_names_in_referral_links] == 1) {
      $userinfo[name] = preg_replace('/\\s+/', '_', $userinfo[name]);
      $userinfo[username] = $userinfo[name];
  }

  $smarty->assign('user', $userinfo);
  $smarty->display('referal.links.tpl');
