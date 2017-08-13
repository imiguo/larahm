<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

header('Content-type: text/vnd.wap.wml');
  $smarty->assign('frm', $frm);
  $smarty->display('wap/login.tpl');
