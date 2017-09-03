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
use App\Exceptions\RedirectException;

if (app('data')->frm['action'] == 'confirm') {
    view_assign('say', app('data')->frm['say']);
    view_execute('edit_account_confirmation.blade.php');
    throw new EmptyException();
}

if (app('data')->frm['action'] == 'edit_account') {
    $errors = [];
    if (app('data')->frm['action2'] != 'confirm') {
        if (app('data')->frm['fullname'] == '') {
            array_push($errors, 'full_name');
        }

        if (app('data')->frm['password'] != '') {
            if (0 < app('data')->settings['min_user_password_length']) {
                if (strlen(app('data')->frm['password']) < app('data')->settings['min_user_password_length']) {
                    array_push($errors, 'password_too_small');
                }
            }

            if (app('data')->frm['password'] != app('data')->frm['password2']) {
                array_push($errors, 'password_confirm');
            }
        }

        if ((app('data')->settings['usercanchangeemail'] and app('data')->frm['email'] == '')) {
            array_push($errors, 'email');
        }

        if (app('data')->settings['use_user_location']) {
            if (app('data')->frm['address'] == '') {
                array_push($errors, 'address');
            }

            if (app('data')->frm['city'] == '') {
                array_push($errors, 'city');
            }

            if (app('data')->frm['state'] == '') {
                array_push($errors, 'state');
            }

            if (app('data')->frm['zip'] == '') {
                array_push($errors, 'zip');
            }

            if (app('data')->frm['zip'] == '') {
                array_push($errors, 'country');
            }
        }

        if (app('data')->settings['use_transaction_code']) {
            if (app('data')->frm['transaction_code'] != '') {
                if (app('data')->frm['transaction_code_current'] == $userinfo['transaction_code']) {
                    if (0 < app('data')->settings['min_user_password_length']) {
                        if (strlen(app('data')->frm['transaction_code']) < app('data')->settings['min_user_password_length']) {
                            array_push($errors, 'transaction_code_too_small');
                        }
                    }

                    if (app('data')->frm['transaction_code'] != app('data')->frm['transaction_code2']) {
                        array_push($errors, 'transaction_code_confirm');
                    }
                } else {
                    array_push($errors, 'invalid_transaction_code');
                }
            }

            if (((app('data')->frm['transaction_code'] != '' and app('data')->frm['password'] != '') and app('data')->frm['transaction_code'] == app('data')->frm['password'])) {
                array_push($errors, 'transaction_code_vs_password');
            }
        }
    }

    if (sizeof($errors) == 0) {
        if (app('data')->settings['account_update_confirmation'] == 1) {
            if (app('data')->frm['action2'] == 'confirm') {
                if (session('account_update_confirmation_code') == app('data')->frm['account_update_confirmation_code']) {
                    if (is_array(session('fields'))) {
                        app('data')->frm = array_merge(app('data')->frm, session('fields'));
                    } else {
                        throw new RedirectException('/?a=edit_account');
                    }
                } else {
                    throw new RedirectException('/?a=edit_account&action=confirm&say=invalid_code');
                }
            } else {
                $code = get_rand_md5(50);
                session(['account_update_confirmation_code' => $code]);
                session(['fields' => app('data')->frm]);
                $info = [];
                $info['confirmation_code'] = $code;
                $info['username'] = $userinfo['username'];
                $info['name'] = $userinfo['name'];
                $info['ip'] = app('data')->env['REMOTE_ADDR'];
                send_template_mail('account_update_confirmation', $userinfo['email'], $info);
                throw new RedirectException('/?a=edit_account&action=confirm');
            }
        }

        $fullname = quote(app('data')->frm['fullname']);
        $password = quote(app('data')->frm['password']);
        $enc_password = bcrypt($password);
        $email = quote(app('data')->frm['email']);

        $perfectmoney = quote(app('data')->frm['perfectmoney_account']);
        $payeer = quote(app('data')->frm['payeer_account']);
        $bitcoin = quote(app('data')->frm['bitcoin_account']);

        $address = quote(app('data')->frm['address']);
        $city = quote(app('data')->frm['city']);
        $state = quote(app('data')->frm['state']);
        $zip = quote(app('data')->frm['zip']);
        $country = quote(app('data')->frm['country']);
        $transaction_code = quote(app('data')->frm['transaction_code']);

        if (($userinfo['email'] != app('data')->frm['email'] and app('data')->settings['usercanchangeemail'] == 1)) {
            $q = 'update users set email = \''.$email.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        if (($userinfo['perfectmoney_account'] != app('data')->frm['perfectmoney_account'] and app('data')->settings['usercanchangeperfectmoneyacc'])) {
            $q = 'update users set perfectmoney_account = \''.$perfectmoney.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        if (($userinfo['payeer_account'] != app('data')->frm['payeer_account'] and app('data')->settings['usercanchangepayeeracc'])) {
            $q = 'update users set payeer_account = \''.$payeer.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        if (($userinfo['bitcoin_account'] != app('data')->frm['bitcoin_account'] and app('data')->settings['usercanchangebitcoinacc'])) {
            $q = 'update users set bitcoin_account = \''.$bitcoin.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        if (app('data')->frm['password'] != '') {
            $q = 'update users set password = \''.$enc_password.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
            if (app('data')->settings['store_uncrypted_password'] == 1) {
                $pswd = quote(app('data')->frm['password']);
                $q = 'update users set pswd = \''.$pswd.'\' where id > 1 and id = '.$userinfo['id'];
                db_query($q);
            }
        }

        if (app('data')->frm['transaction_code'] != '') {
            $q = 'update users set transaction_code = \''.$transaction_code.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        if (app('data')->frm['wappassword'] != '') {
            $enc_password = quote(bcrypt(app('data')->frm['wappassword']));
            $q = 'update users set stat_password = \''.$enc_password.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        $edit_location = '';
        if (app('data')->settings['use_user_location']) {
            $edit_location = 'address = \''.$address.'\',
                          city = \''.$city.'\',
                          state = \''.$state.'\',
                          zip = \''.$zip.'\',
                          country = \''.$country.'\',
                         ';
        }

        $user_auto_pay_earning = sprintf('%d', app('data')->frm['user_auto_pay_earning']);
        $q = 'update users set name = \''.$fullname.'\',
                 '.$edit_location.'
                 perfectmoney_account = \''.$perfectmoney.'\',
                 payeer_account = \''.$payeer.'\',
                 bitcoin_account = \''.$bitcoin.'\',
                 user_auto_pay_earning = '.$user_auto_pay_earning.'
                 where id > 1 and id = '.$userinfo['id'];
        db_query($q);
        $q = 'select * from users where id ='.$userinfo['id'];
        $sth = db_query($q);
        $userinfo = mysql_fetch_array($sth);
        $userinfo['logged'] = 1;

        // 发邮件通知
        if (app('data')->settings['sendnotify_when_userinfo_changed'] == 1) {
            $info = [];
            $info['username'] = $userinfo['username'];
            $info['password'] = $password;
            $info['name'] = $userinfo['name'];
            $info['ip'] = app('data')->env['REMOTE_ADDR'];
            $info['perfectmoney'] = $userinfo['perfectmoney_account'];
            $info['payeer'] = $userinfo['payeer_account'];
            $info['bitcoin'] = $userinfo['bitcoin_account'];
            $info['email'] = $userinfo['email'];
            if (app('data')->frm['email'] == '') {
                app('data')->frm['email'] = $userinfo['email'];
            }

            send_template_mail('change_account', app('data')->frm['email'], $info);
        }

        throw new RedirectException('/?a=edit_account&say=changed');
    }
}

include app_path('Hm').'/inc/countries.php';
$q = 'select date_format(\''.$userinfo['date_register'].'\' + interval '.app('data')->settings['time_dif'].' day, \'%b-%e-%Y %r\') as date_registered';
$sth = db_query($q);
$row = mysql_fetch_array($sth);
$userinfo['date_register'] = $row['date_registered'];
view_assign('userinfo', $userinfo);
view_assign('errors', $errors);
view_assign('frm', app('data')->frm);
view_assign('settings', app('data')->settings);
view_assign('countries', $countries);
view_execute('edit_account.blade.php');
