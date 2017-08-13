<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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

function try_auth($password, &$userinfo)
{
    global $frm;
    global $settings;
    global $frm_env;

    $ip = $frm_env['REMOTE_ADDR'];
    $add_login_check = ' and last_access_time + interval 30 minute > now() and last_access_ip = \''.$ip.'\'';
    if ($settings['demomode'] == 1) {
        $add_login_check = '';
    }
    list($user_id, $chid) = explode('-', $password, 2);
    $user_id = sprintf('%d', $user_id);
    $chid = quote($chid);
    if ($settings['htaccess_authentication'] == 1) {
        $login = $frm_env['PHP_AUTH_USER'];
        $password = $frm_env['PHP_AUTH_PW'];
        $q = 'select * from hm2_users where id = 1';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            if (($login == $row['username'] and md5($password) == $row['password'])) {
                $userinfo = $row;
                $userinfo[logged] = 1;
                continue;
            }
        }

        if ($userinfo[logged] != 1) {
            header('WWW-Authenticate: Basic realm="Authorization Required!"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Authorization Required!';
            exit;
        }
    } else {
        if ($settings['htpasswd_authentication'] == 1) {
            if ((file_exists('./.htpasswd') and file_exists('./.htaccess'))) {
                $q = 'select * from hm2_users where id = 1';
                $sth = db_query($q);
                while ($row = mysql_fetch_array($sth)) {
                    $userinfo = $row;
                    $userinfo[logged] = 1;
                }
            }
        } else {
            $q = 'select *, date_format(date_register + interval '.$settings['time_dif'].(''.' day, \'%b-%e-%Y\') as create_account_date, l_e_t + interval 15 minute < now() as should_count from hm2_users where id = '.$user_id.' and (status=\'on\' or status=\'suspended\') '.$add_login_check.' and id = 1');
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                if (($settings['brute_force_handler'] == 1 and $row['activation_code'] != '')) {
                    header('Location: /?a=login&say=invalid_login&username='.$frm['username']);
                    exit;
                }

                $qhid = $row['hid'];
                $hid = substr($qhid, 5, 20);
                if ($chid == md5($hid)) {
                    $userinfo = $row;
                    $userinfo['logged'] = 1;
                    $q = 'update hm2_users set last_access_time = now() where id = 1';
                    db_query($q);
                    continue;
                } else {
                    $q = 'update hm2_users set bf_counter = bf_counter + 1 where id = '.$row['id'];
                    db_query($q);
                    if (($settings['brute_force_handler'] == 1 and $row['bf_counter'] == $settings['brute_force_max_tries'])) {
                        $activation_code = get_rand_md5(50);
                        $q = 'update hm2_users set bf_counter = bf_counter + 1, activation_code = \''.$activation_code.'\' where id = '.$row['id'];
                        db_query($q);
                        $info = [];
                        $info['activation_code'] = $activation_code;
                        $info['username'] = $row['username'];
                        $info['name'] = $row['name'];
                        $info['ip'] = $frm_env['REMOTE_ADDR'];
                        $info['max_tries'] = $settings['brute_force_max_tries'];
                        send_template_mail('brute_force_activation', $row['email'], $settings['system_email'], $info);
                        header('Location: /?a=login&say=invalid_login&username='.$frm['username']);
                        exit;
                        continue;
                    }

                    continue;
                }
            }
        }
    }

    if ($userinfo['logged'] != 1) {
        header('Location: /');
        exit;
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
    header('Location: ?a=startup_bonus&say=yes');
}

function save_exchange_rates()
{
    global $frm;
    global $settings;
    global $exchange_systems;

    if ($settings['demomode']) {
        header('Location: ?a=exchange_rates&say=demo');
        exit;
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

    header('Location: ?a=exchange_rates');
}
