<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Illuminate\Support\Facades\Auth;
use App\Exceptions\RedirectException;

function show_program_stat()
{
    global $frm;
    $login = quote($frm['login']);
    $q = 'select * from hm2_users where id = 1 and username = \''.$login.'\' and stat_password <> \'\'';
    $sth = db_query($q);
    $flag = 0;
    while ($row = mysql_fetch_array($sth)) {
        if ($row['stat_password'] == md5($frm['password'])) {
            $flag = 1;
            continue;
        }
    }

    if ($flag == 0) {
        echo '<center>Wrong login or password</center>';
    } else {
        if ($frm['page'] == 'members') {
            include app_path('Hm').'/inc/admin/members_program.inc.php';
        } else {
            if ($frm['page'] == 'pendingwithdrawal') {
                include app_path('Hm').'/inc/admin/pending_program.inc.php';
            } else {
                if ($frm['page'] == 'whoonline') {
                    include app_path('Hm').'/inc/admin/whoonline_program.inc.php';
                } else {
                    if ($frm['page'] == 'TrayInfo') {
                        include app_path('Hm').'/inc/admin/tray_info.php';
                    } else {
                        include app_path('Hm').'/inc/admin/main_program.inc.php';
                    }
                }
            }
        }
    }
}

function try_auth(&$userinfo)
{
    if (Auth::check() && Auth::id() == 1) {
        $q = 'select * from hm2_users where id = 1';
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $userinfo = $row;
        $userinfo['logged'] = 1;
    } else {
        throw new RedirectException('/');
    }
}

function startup_bonus()
{
    global $frm;
    global $settings;

    $settings['startup_bonus'] = sprintf('%0.2f', $frm['startup_bonus']);
    $settings['startup_bonus_ec'] = sprintf('%d', $frm['ec']);
    $settings['forbid_withdraw_before_deposit'] = ($frm['forbid_withdraw_before_deposit'] ? 1 : 0);
    $settings['activation_fee'] = sprintf('%0.2f', $frm['activation_fee']);
    save_settings();
    throw new RedirectException('/?a=startup_bonus&say=yes');
}

function save_exchange_rates()
{
    global $frm;
    global $settings;
    global $exchange_systems;

    if ($settings['demomode']) {
        throw new RedirectException('/?a=exchange_rates&say=demo');
    }

    $exch = $frm['exch'];
    if (is_array($exch)) {
        foreach ($exchange_systems as $id_from => $value) {
            foreach ($exchange_systems as $id_to => $value) {
                if ($id_to == $id_from) {
                    continue;
                }

                $percent = sprintf('%.02f', $exch[$id_from][$id_to]);
                if ($percent < 0) {
                    $percent = 0;
                }

                if (100 < $percent) {
                    $percent = 100;
                }

                $q = 'select count(*) as cnt from hm2_exchange_rates where `sfrom` = '.$id_from.' and `sto` = '.$id_to;
                $sth = db_query($q);
                $row = mysql_fetch_array($sth);
                if (0 < $row['cnt']) {
                    $q = 'update hm2_exchange_rates set percent = '.$percent.' where `sfrom` = '.$id_from.' and `sto` = '.$id_to;
                } else {
                    $q = 'insert into hm2_exchange_rates set percent = '.$percent.', `sfrom` = '.$id_from.', `sto` = '.$id_to;
                }

                db_query($q);
            }
        }
    }

    throw new RedirectException('/?a=exchange_rates');
}
