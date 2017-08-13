<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require app_path('Hm').'/lib/config.inc.php';

$smarty = app('smarty');

if ($settings['accesswap'] == 0) {
    exit();
}

$userinfo = [];
$userinfo['logged'] = 0;
$q = 'delete from hm2_online where ip=\''.$frm_env['REMOTE_ADDR'].'\' or date + interval 30 minute < now()';
db_query($q);
$q = 'insert into hm2_online set ip=\''.$frm_env['REMOTE_ADDR'].'\', date = now()';
db_query($q);
if ($frm['a'] == 'logout') {
    setcookie('username', '', time() + 630720000);
    setcookie('password', '', time() + 630720000);
    $frm_cookie['username'] = '';
    $frm_cookie['password'] = '';
}

$smarty->assign('settings', $settings);
if ($frm['a'] == 'do_login') {
    $username = quote($frm['username']);
    $password = quote($frm['password']);
    $password = md5($password);
    $add_opt_in_check = '';
    if ($settings['use_opt_in'] == 1) {
        $add_opt_in_check = ' and (confirm_string = "" or confirm_string is NULL)';
    }

    $q = 'select *, date_format(date_register + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y\') as create_account_date from hm2_users where username = \''.$username.'\' and stat_password = \''.$password.'\' and stat_password <> \'\' and (status=\'on\' or status=\'suspended\') '.$add_opt_in_check);
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $userinfo = $row;
        $userinfo['logged'] = 1;
    }

    if ($userinfo['logged'] == 0) {
        header('Location: wap.php?a=login&say=invalid_login&username='.$frm['username']);
        exit();
    } else {
        $ip = $frm_env['REMOTE_ADDR'];
        $q = 'insert into hm2_user_access_log set user_id = '.$userinfo['id'].(''.',date = now(), ip = \''.$ip.'\'');
        if (!(db_query($q))) {
        }

        setcookie('username', $frm['username'], time() + 630720000);
        setcookie('password', md5($frm['password']), time() + 630720000);
        $ip = $frm_env['REMOTE_ADDR'];
        $q = 'update hm2_users set last_access_time = now(), last_access_ip = \''.$ip.'\' where username=\''.$username.'\'';
        if (!(db_query($q))) {
        }
    }

    if (($userinfo['logged'] == 1 and $userinfo['id'] == 1)) {
        setcookie('username', $frm['username'], time() + 630720000);
        setcookie('password', md5($frm['password']), time() + 630720000);
        header('Location: wap.php?ok');
        exit();
    }
} else {
    $username = quote($frm_cookie['username']);
    $password = $frm_cookie['password'];
    $ip = $frm_env['REMOTE_ADDR'];
    $add_login_check = ' and last_access_time + interval 30 minute > now() and last_access_ip = \''.$ip.'\'';
    if ($settings['demomode'] == 1) {
        $add_login_check = '';
    }

    $q = 'select *, date_format(date_register + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y\') as create_account_date from hm2_users where username = \''.$username.'\' and (status=\'on\' or status=\'suspended\') '.$add_login_check);
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        if ($password == $row['stat_password']) {
            $userinfo = $row;
            $userinfo['logged'] = 1;
            $q = 'update hm2_users set last_access_time = now() where username=\''.$username.'\'';
            if (!(db_query($q))) {
            }

            continue;
        }
    }
}

if ($userinfo['logged'] == 1) {
    count_earning($userinfo['id']);
}

$smarty->assign('userinfo', $userinfo);
if ($frm['a'] == 'login') {
    include app_path('Hm').'/inc/wap/login.inc';
} else {
    if ((($frm['a'] == 'do_login' or $frm['a'] == 'account') and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/wap/account_main.inc';
    } else {
        if (($frm['a'] == 'earnings' and $userinfo['logged'] == 1)) {
            include app_path('Hm').'/inc/wap/earning_history.inc';
        } else {
            if (($frm['a'] == 'admin_pending' and $userinfo['id'] == 1)) {
                include app_path('Hm').'/inc/admin/wap/pending.inc.php';
            } else {
                if ($userinfo['id'] == 1) {
                    include app_path('Hm').'/inc/admin/wap/main.inc.php';
                    exit();
                } else {
                    include app_path('Hm').'/inc/wap/home.inc';
                    exit();
                }
            }
        }
    }
}

exit();
