<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Exceptions\EmptyException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\RedirectException;
use Illuminate\Support\Facades\Cookie;

require app_path('Hm').'/lib/index.php';

$smarty = app('smarty');
$smarty->default_modifiers = ['escape'];

if (isset(app('data')->frm['ref']) && app('data')->frm['ref'] != '') {
    bind_ref();
}

if (app('data')->frm['a'] == 'run_crontab') {
    count_earning(-2);
    throw new EmptyException();
}

$q = 'delete from online where ip=\''.app('data')->env['REMOTE_ADDR'].'\' or date + interval 30 minute < now()';
db_query($q);
$q = 'insert into online set ip=\''.app('data')->env['REMOTE_ADDR'].'\', date = now()';
db_query($q);

$userinfo = [];
$userinfo['logged'] = 0;
if (app('data')->frm['a'] == 'logout') {
    Auth::logout();
}

$stats = [];
show_info_box($stats);

$ref = Cookie::get('referer', '');
if ($ref) {
    $q = 'select * from users where username = \''.$ref.'\'';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        view_assign('referer', $row);
    }
}

view_assign('settings', app('data')->settings);

if (app('data')->frm['a'] == 'do_login') {
    do_login($userinfo);
    Auth::loginUsingId($userinfo['id']);

    if (app('data')->identity = max(app('data')->identity, Auth::user()->identity)) {
        Cookie::queue('identity', app('data')->identity, 43200);
    }

    if (Auth::user()->identity != app('data')->identity && app('data')->identity == User::IDENTITY_MONITOR) {
        Auth::user()->bad = true;
        Auth::user()->save();
    }

    if (($userinfo['logged'] == 1 and $userinfo['id'] == 1)) {
        add_log('Admin logged', 'Admin entered to admin area ip='.app('data')->env['REMOTE_ADDR']);

        $admin_url = env('ADMIN_URL');
        $html = "<head><title>HYIP Manager</title><meta http-equiv=\"Refresh\" content=\"1; URL={$admin_url}\"></head>";
        $html .= "<body><center><a href=\"{$admin_url}\">Go to admin area</a></center></body>";
        echo $html;
        throw new EmptyException();
    }
    throw new RedirectException('/?a=account');
}
do_login_else($userinfo);

if (($userinfo['logged'] == 1 and $userinfo['should_count'] == 1)) {
    count_earning($userinfo['id']);
}

if ($userinfo['id'] == 1) {
    $userinfo['logged'] = 0;
}

if ($userinfo['logged'] == 1) {
    $q = 'select type, sum(actual_amount) as s from history where user_id = '.$userinfo['id'].' group by type';
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

if ((app('data')->frm['a'] == 'cancelwithdraw' and $userinfo['logged'] == 1)) {
    $id = sprintf('%d', app('data')->frm['id']);
    $q = 'delete from history where id = '.$id.' and type=\'withdraw_pending\' and user_id = '.$userinfo['id'];
    db_query($q);
    throw new RedirectException('/?a=withdraw_history');
}

view_assign('userinfo', $userinfo);

view_assign('frm', app('data')->frm);

if (isset($userinfo['id']) && $id = $userinfo['id']) {
    $ab = get_user_balance($id);
    $ab_formated = [];
    $ab['deposit'] = 0 - $ab['deposit'];
    $ab['earning'] = $ab['earning'];
    reset($ab);
    while (list($kk, $vv) = each($ab)) {
        $ab_formated[$kk] = number_format(abs($vv), 2);
    }

    view_assign('currency_sign', '$');
    view_assign('ab_formated', $ab_formated);
}

if ((app('data')->frm['a'] == 'signup' and $userinfo['logged'] != 1)) {
    include app_path('Hm').'/inc/signup.php';
} elseif ((app('data')->frm['a'] == 'forgot_password' and $userinfo['logged'] != 1)) {
    include app_path('Hm').'/inc/forgot_password.php';
} elseif ((app('data')->frm['a'] == 'confirm_registration' and app('data')->settings['use_opt_in'] == 1)) {
    include app_path('Hm').'/inc/confirm_registration.php';
} elseif (app('data')->frm['a'] == 'login') {
    include app_path('Hm').'/inc/login.php';
} elseif (((app('data')->frm['a'] == 'do_login' or app('data')->frm['a'] == 'account') and $userinfo['logged'] == 1)) {
    include app_path('Hm').'/inc/account_main.php';
} elseif ((app('data')->frm['a'] == 'deposit' and $userinfo['logged'] == 1)) {
    if (substr(app('data')->frm['type'], 0, 8) == 'account_') {
        $ps = substr(app('data')->frm['type'], 8);
        if (app('data')->exchange_systems[$ps]['status'] == 1) {
            include app_path('Hm').'/inc/deposit.account.confirm.php';
        } else {
            include app_path('Hm').'/inc/deposit.php';
        }
    } else {
        if (substr(app('data')->frm['type'], 0, 8) == 'process_') {
            $ps = substr(app('data')->frm['type'], 8);
            if (app('data')->exchange_systems[$ps]['status'] == 1) {
                switch ($ps) {
                    case 1:
                        include app_path('Hm').'/inc/deposit.perfectmoney.confirm.php';
                        break;
                    case 2:
                        include app_path('Hm').'/inc/deposit.payeer.confirm.php';
                        break;
                    case 3:
                        include app_path('Hm').'/inc/deposit.bitcoin.confirm.php';
                        break;
                    default:
                        include app_path('Hm').'/inc/deposit.other.confirm.php';
                }
            } else {
                include app_path('Hm').'/inc/deposit.php';
            }
        } else {
            include app_path('Hm').'/inc/deposit.php';
        }
    }
} else {
    if (((app('data')->frm['a'] == 'add_funds' and app('data')->settings['use_add_funds'] == 1) and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/add_funds.php';
    } elseif ((app('data')->frm['a'] == 'withdraw' and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/withdrawal.php';
    } elseif ((app('data')->frm['a'] == 'withdraw_history' and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/withdrawal_history.php';
    } elseif ((app('data')->frm['a'] == 'deposit_history' and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/deposit_history.php';
    } elseif ((app('data')->frm['a'] == 'earnings' and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/earning_history.php';
    } elseif ((app('data')->frm['a'] == 'deposit_list' and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/deposit_list.php';
    } elseif ((app('data')->frm['a'] == 'edit_account' and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/edit_account.php';
    } elseif ((app('data')->frm['a'] == 'withdraw_principal' and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/withdraw_principal.php';
    } elseif ((app('data')->frm['a'] == 'change_compound' and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/change_compound.php';
    } elseif ((app('data')->frm['a'] == 'internal_transfer' and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/internal_transfer.php';
    } elseif (app('data')->frm['a'] == 'support') {
        include app_path('Hm').'/inc/support.php';
    } elseif (app('data')->frm['a'] == 'faq') {
        include app_path('Hm').'/inc/faq.php';
    } elseif (app('data')->frm['a'] == 'company') {
        include app_path('Hm').'/inc/company.php';
    } elseif (app('data')->frm['a'] == 'rules') {
        include app_path('Hm').'/inc/rules.php';
    } elseif (((app('data')->frm['a'] == 'members_stats' and app('data')->settings['show_stats_box']) and app('data')->settings['show_members_stats'])) {
        include app_path('Hm').'/inc/members_stats.php';
    } elseif (((app('data')->frm['a'] == 'paidout' and app('data')->settings['show_stats_box']) and app('data')->settings['show_paidout_stats'])) {
        include app_path('Hm').'/inc/paidout.php';
    } elseif (((app('data')->frm['a'] == 'top10' and app('data')->settings['show_stats_box']) and app('data')->settings['show_top10_stats'])) {
        include app_path('Hm').'/inc/top10.php';
    } elseif (((app('data')->frm['a'] == 'last10' and app('data')->settings['show_stats_box']) and app('data')->settings['show_last10_stats'])) {
        include app_path('Hm').'/inc/last10.php';
    } elseif (((app('data')->frm['a'] == 'refs10' and app('data')->settings['show_stats_box']) and app('data')->settings['show_refs10_stats'])) {
        include app_path('Hm').'/inc/refs10.php';
    } elseif ($_GET['a'] == 'return_bitcoin') {
        include app_path('Hm').'/inc/deposit.status.php';
    } elseif ($_GET['a'] == 'return_perfectmoney') {
        include app_path('Hm').'/inc/deposit.status.php';
    } elseif ($_GET['a'] == 'return_payeer') {
        include app_path('Hm').'/inc/deposit.status.php';
    } elseif (((app('data')->frm['a'] == 'referallinks' and app('data')->settings['use_referal_program'] == 1) and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/referal.links.php';
    } elseif (((app('data')->frm['a'] == 'referals' and app('data')->settings['use_referal_program'] == 1) and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/referals.php';
    } elseif (app('data')->frm['a'] == 'news') {
        include app_path('Hm').'/inc/news.php';
    } elseif (app('data')->frm['a'] == 'calendar') {
        include app_path('Hm').'/inc/calendar.php';
    } elseif ((app('data')->frm['a'] == 'exchange' and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/exchange.php';
    } elseif ((app('data')->frm['a'] == 'banner' and $userinfo['logged'] == 1)) {
        include app_path('Hm').'/inc/banner.php';
    } elseif (app('data')->frm['a'] == 'activate') {
        include app_path('Hm').'/inc/activate.php';
    } elseif (app('data')->frm['a'] == 'show_package_info') {
        include app_path('Hm').'/inc/package_info.php';
    } elseif (app('data')->frm['a'] == 'ref_plans') {
        include app_path('Hm').'/inc/ref_plans.php';
    } else {
        if (app('data')->frm['a'] == 'cust') {
            $file = app('data')->frm['page'];
            $file = basename($file);
            if (file_exists(tmpl_path().'/custom/'.$file.'.tpl')) {
                view_execute('custom/'.$file.'.blade.php');
            } else {
                include app_path('Hm').'/inc/home.php';
            }
        } else {
            if (app('data')->frm['a'] == 'invest_page') {
                view_assign('frm', app('data')->frm);
                include app_path('Hm').'/inc/invest_page.php';
            } else {
                view_assign('frm', app('data')->frm);
                include app_path('Hm').'/inc/home.php';
            }
        }
    }
}
