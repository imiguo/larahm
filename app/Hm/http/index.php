<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require HM_PATH.'/lib/config.inc.php';
require HM_PATH.'/lib/index.inc.php';

$smarty = app('smarty');
$smarty->default_modifiers = ['escape'];

if (HTTPS) {
    $frm_env['HTTPS'] = 1;
}

if (isset($frm['ref']) && $frm['ref'] != '') {
    bind_ref();
}

if ((empty($frm_env['HTTPS']) and isset($settings['redirect_to_https']) and $settings['redirect_to_https'] == 1)) {
    redirect_https();
    exit;
}

if ((isset($frm['CUSTOM2']) && $frm['CUSTOM2'] == 'pay_withdraw_eeecurrency' and $frm['TRANSACTION_ID'] != '')) {
    // custom2_pay_withdraw_eeecurrency();
    echo 1;
    exit;
}

if ((isset($frm['CUSTOM2']) && $frm['CUSTOM2'] == 'pay_withdraw' and $frm['TRANSACTION_ID'] != '')) {
    // custom2_pay_withdraw();
    echo 1;
    exit;
}

if (((isset($frm['user3']) and $frm['user3'] == 'pay_withdraw' and $frm['transaction_id'] != '') and $frm['transaction_type'] == 'Payment')) {
    // user3_pay_withdraw_payment();
    echo 1;
    exit;
}

if ($frm['a'] == 'run_crontab') {
    count_earning(-2);
    exit;
}

$q = 'delete from hm2_online where ip=\''.$frm_env['REMOTE_ADDR'].'\' or date + interval 30 minute < now()';
db_query($q);
$q = 'insert into hm2_online set ip=\''.$frm_env['REMOTE_ADDR'].'\', date = now()';
db_query($q);

$userinfo = [];
$userinfo['logged'] = 0;
if ($frm['a'] == 'logout') {
    setcookie('password', 'deleted', time() + 630720000);
    $frm_cookie['username'] = '';
    $frm_cookie['password'] = '';
    if ($settings['redirect_logout'] != '') {
        header('Location: '.$settings['redirect_logout']);
        exit;
    }

    $frm['a'] = '';
}

if ($frm['a'] == 'home') {
    $frm['a'] = '';
}

$stats = [];
if ($settings['crontab_stats'] == 1) {
    $s = file('stats.php');
    $stats = unserialize($s[0]);
}

show_info_box();

$ref = isset($frm_cookie['Referer']) ? quote($frm_cookie['Referer']) : '';
if ($ref) {
    $q = 'select * from hm2_users where username = \''.$ref.'\'';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $smarty->assign('referer', $row);
    }
}

$smarty->assign('settings', $settings);

if ($frm['a'] == 'do_login') {
    do_login($userinfo);
} else {
    do_login_else($userinfo);
}

if (($userinfo['logged'] == 1 and $userinfo['should_count'] == 1)) {
    count_earning($userinfo['id']);
}

if ($userinfo['id'] == 1) {
    $userinfo['logged'] = 0;
}

if ($userinfo['logged'] == 1) {
    $q = 'select type, sum(actual_amount) as s from hm2_history where user_id = '.$userinfo['id'].' group by type';
    $sth = db_query($q);
    $balance = 0;
    while ($row = mysql_fetch_array($sth)) {
        if ($row['type'] == 'deposit') {
            $userinfo['total_deposited'] = number_format(abs($row['s']), 2);
        }

        if ($row['type'] == 'earning') {
            $userinfo['total_earned'] = number_format(abs($row['s']), 2);
        }

        $balance += $row['s'];
    }

    $userinfo['balance'] = number_format(abs($balance), 2);
}

if (((((($frm['a'] != 'show_validation_image' and !$userinfo['logged']) and extension_loaded('gd')) and $settings['graph_validation'] == 1) and 0 < $settings['graph_max_chars']) and $frm['action'] != 'signup')) {
    $userinfo['validation_enabled'] = 1;
    session_start();
    $validation_number = gen_confirm_code($settings['graph_max_chars'], 0);
    if ($settings['use_number_validation_number']) {
        $i = 0;
        $validation_number = '';
        while ($i < $settings['graph_max_chars']) {
            $validation_number .= rand(0, 9);
            ++$i;
        }
    }

    $_SESSION['validation_number'] = $validation_number;
    session_register('validation_number');
    $userinfo['session_name'] = session_name();
    $userinfo['session_id'] = session_id();
    $userinfo['rand'] = rand();
}

if (($frm['a'] == 'deletewappass' and $userinfo['logged'] == 1)) {
    $id = sprintf('%d', $userinfo['id']);
    $q = 'update hm2_users set stat_password = \'\' where id = '.$id;
    db_query($q);
    header('Location: ?a=edit_account');
    exit;
}

if (($frm['a'] == 'cancelwithdraw' and $userinfo['logged'] == 1)) {
    $id = sprintf('%d', $frm['id']);
    $q = 'delete from hm2_history where id = '.$id.' and type=\'withdraw_pending\' and user_id = '.$userinfo['id'];
    db_query($q);
    header('Location: ?a=withdraw_history');
    exit;
}

$smarty->assign('userinfo', $userinfo);

if ($frm['a'] == 'home') {
    $frm['a'] == '';
}

$smarty->assign('frm', $frm);

if ($settings['banner_extension'] == 1) {
    if ($frm['a'] == 'show_banner') {
        $id = sprintf('%d', $frm['id']);
        $f = @fopen(CACHE_PATH.'/banners/'.$id, 'rb');
        if ($f) {
            $contents = fread($f, filesize(CACHE_PATH.'/banners/'.$id));
            header('Content-type: image/gif');
            echo $contents;
            fclose($fd);
        }

        if ($frm['imps'] != 'no') {
            $q = 'update hm2_users set imps = imps -1 where imps > 0 and id = '.$id;
            db_query($q);
        }

        exit;
    }

    $q = 'select count(*) as col from hm2_users where imps > 0 and bnum > 0';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $z = rand(1, $row['col']) - 1;
        $q = 'select bnum, burl from hm2_users where imps > 0 and bnum > 0 order by id limit '.$z.', 1';
        ($sth1 = db_query($q));
        while ($row1 = mysql_fetch_array($sth1)) {
            $smarty->assign('banner_ext_bnum', $row1['bnum']);
            $smarty->assign('banner_ext_burl', $row1['burl']);
        }
    }
}

if (isset($userinfo['id']) && $id = $userinfo['id']) {
    $ab = get_user_balance($id);
    $ab_formated = [];
    $ab['deposit'] = 0 - $ab['deposit'];
    $ab['earning'] = $ab['earning'];
    reset($ab);
    while (list($kk, $vv) = each($ab)) {
        $ab_formated[$kk] = number_format(abs($vv), 2);
    }

    $smarty->assign('currency_sign', '$');
    $smarty->assign('ab_formated', $ab_formated);
}

include HM_PATH.'/inc/news_box.inc';
if (($frm['a'] == 'signup' and $userinfo['logged'] != 1)) {
    include HM_PATH.'/inc/signup.inc';
    exit;
}
if (($frm['a'] == 'forgot_password' and $userinfo['logged'] != 1)) {
    include HM_PATH.'/inc/forgot_password.inc';
    exit;
}
if (($frm['a'] == 'confirm_registration' and $settings['use_opt_in'] == 1)) {
    include HM_PATH.'/inc/confirm_registration.inc';
    exit;
}
if ($frm['a'] == 'login') {
    include HM_PATH.'/inc/login.inc';
    exit;
}
if ((($frm['a'] == 'do_login' or $frm['a'] == 'account') and $userinfo['logged'] == 1)) {
    include HM_PATH.'/inc/account_main.inc';
    exit;
}
if (($frm['a'] == 'deposit' and $userinfo['logged'] == 1)) {
    if (substr($frm['type'], 0, 8) == 'account_') {
        $ps = substr($frm['type'], 8);
        if ($exchange_systems[$ps]['status'] == 1) {
            include HM_PATH.'/inc/deposit.account.confirm.inc';
        } else {
            include HM_PATH.'/inc/deposit.inc';
        }
    } else {
        if (substr($frm['type'], 0, 8) == 'process_') {
            $ps = substr($frm['type'], 8);
            if ($exchange_systems[$ps]['status'] == 1) {
                switch ($ps) {
                    case 0:
                        include HM_PATH.'/inc/deposit.egold.confirm.inc';
                        break;
                    case 1:
                        include HM_PATH.'/inc/deposit.evocash.confirm.inc';
                        break;
                    case 2:
                        include HM_PATH.'/inc/deposit.intgold.confirm.inc';
                        break;
                    case 3:
                        include HM_PATH.'/inc/deposit.perfectmoney.confirm.inc';
                        break;
                    case 4:
                        include HM_PATH.'/inc/deposit.stormpay.confirm.inc';
                        break;
                    case 5:
                        include HM_PATH.'/inc/deposit.ebullion.confirm.inc';
                        break;
                    case 6:
                        include HM_PATH.'/inc/deposit.paypal.confirm.inc';
                        break;
                    case 7:
                        include HM_PATH.'/inc/deposit.goldmoney.confirm.inc';
                        break;
                    case 8:
                        include HM_PATH.'/inc/deposit.eeecurrency.confirm.inc';
                        break;
                    case 9:
                        include HM_PATH.'/inc/deposit.pecunix.confirm.inc';
                        break;
                    case 1:
                        include HM_PATH.'/inc/deposit.payeer.confirm.inc';
                        break;
                    case 1:
                        include HM_PATH.'/inc/deposit.bitcoin.confirm.inc';
                        break;
                    default:
                        include HM_PATH.'/inc/deposit.other.confirm.inc';
                }
            } else {
                include HM_PATH.'/inc/deposit.inc';
            }
        } else {
            include HM_PATH.'/inc/deposit.inc';
        }
    }
} else {
    if ((($frm['a'] == 'add_funds' and $settings['use_add_funds'] == 1) and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/add_funds.inc';
        exit;
    }
    if (($frm['a'] == 'withdraw' and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/withdrawal.inc';
        exit;
    }
    if (($frm['a'] == 'withdraw_history' and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/withdrawal_history.inc';
        exit;
    }
    if (($frm['a'] == 'deposit_history' and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/deposit_history.inc';
        exit;
    }
    if (($frm['a'] == 'earnings' and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/earning_history.inc';
        exit;
    }
    if (($frm['a'] == 'deposit_list' and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/deposit_list.inc';
        exit;
    }
    if (($frm['a'] == 'edit_account' and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/edit_account.inc';
        exit;
    }
    if (($frm['a'] == 'withdraw_principal' and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/withdraw_principal.inc';
        exit;
    }
    if (($frm['a'] == 'change_compound' and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/change_compound.inc';
        exit;
    }
    if (($frm['a'] == 'internal_transfer' and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/internal_transfer.inc';
        exit;
    }
    if ($frm['a'] == 'support') {
        include HM_PATH.'/inc/support.inc';
        exit;
    }
    if ($frm['a'] == 'faq') {
        include HM_PATH.'/inc/faq.inc';
        exit;
    }
    if ($frm['a'] == 'company') {
        include HM_PATH.'/inc/company.inc';
        exit;
    }
    if ($frm['a'] == 'rules') {
        include HM_PATH.'/inc/rules.inc';
        exit;
    }
    if ($frm['a'] == 'show_validation_image') {
        include HM_PATH.'/inc/show_validation_image.inc';
        exit;
    }
    if ((($frm['a'] == 'members_stats' and $settings['show_stats_box']) and $settings['show_members_stats'])) {
        include HM_PATH.'/inc/members_stats.inc';
        exit;
    }
    if ((($frm['a'] == 'paidout' and $settings['show_stats_box']) and $settings['show_paidout_stats'])) {
        include HM_PATH.'/inc/paidout.inc';
        exit;
    }
    if ((($frm['a'] == 'top10' and $settings['show_stats_box']) and $settings['show_top10_stats'])) {
        include HM_PATH.'/inc/top10.inc';
        exit;
    }
    if ((($frm['a'] == 'last10' and $settings['show_stats_box']) and $settings['show_last10_stats'])) {
        include HM_PATH.'/inc/last10.inc';
        exit;
    }
    if ((($frm['a'] == 'refs10' and $settings['show_stats_box']) and $settings['show_refs10_stats'])) {
        include HM_PATH.'/inc/refs10.inc';
        exit;
    }
    if ($_GET['a'] == 'return_egold') {
        include HM_PATH.'/inc/deposit.egold.status.inc';
        exit;
    }
    if ($_GET['a'] == 'return_perfectmoney') {
        include HM_PATH.'/inc/deposit.perfectmoney.status.inc';
        exit;
    }
    if ($_GET['a'] == 'return_payeer') {
        include HM_PATH.'/inc/deposit.payeer.status.inc';
        exit;
    }
    if ((($frm['a'] == 'referallinks' and $settings['use_referal_program'] == 1) and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/referal.links.inc';
        exit;
    }
    if ((($frm['a'] == 'referals' and $settings['use_referal_program'] == 1) and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/referals.inc';
        exit;
    }
    if ($frm['a'] == 'news') {
        include HM_PATH.'/inc/news.inc';
        exit;
    }
    if ($frm['a'] == 'calendar') {
        include HM_PATH.'/inc/calendar.inc';
        exit;
    }
    if (($frm['a'] == 'exchange' and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/exchange.inc';
        exit;
    }
    if (($frm['a'] == 'banner' and $userinfo['logged'] == 1)) {
        include HM_PATH.'/inc/banner.inc';
        exit;
    }
    if ($frm['a'] == 'activate') {
        include HM_PATH.'/inc/activate.inc';
        exit;
    }
    if ($frm['a'] == 'show_package_info') {
        include HM_PATH.'/inc/package_info.inc';
        exit;
    }
    if ($frm['a'] == 'ref_plans') {
        include HM_PATH.'/inc/ref_plans.inc';
    } else {
        if ($frm['a'] == 'cust') {
            $file = $frm['page'];
            $file = basename($file);
            if (file_exists(TMPL_PATH.'/custom/'.$file.'.tpl')) {
                $smarty->display('custom/'.$file.'.tpl');
            } else {
                include HM_PATH.'/inc/home.inc';
            }
        } else {
            if ($frm['a'] == 'invest_page') {
                $smarty->assign('frm',
                    $frm);
                include HM_PATH.'/inc/invest_page.inc';
            } else {
                $smarty->assign('frm',
                    $frm);
                include HM_PATH.'/inc/home.inc';
            }
        }
    }
}
