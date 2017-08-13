<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if ($frm['action'] == 'confirm') {
    $smarty->assign('say', $frm['say']);
    $smarty->display('edit_account_confirmation.tpl');
    exit();
}

if ($frm['action'] == 'edit_account') {
    $errors = [];
    if ($frm['action2'] != 'confirm') {
        if ($frm['fullname'] == '') {
            array_push($errors, 'full_name');
        }

        if ($frm['password'] != '') {
            if (0 < $settings['min_user_password_length']) {
                if (strlen($frm['password']) < $settings['min_user_password_length']) {
                    array_push($errors, 'password_too_small');
                }
            }

            if ($frm['password'] != $frm['password2']) {
                array_push($errors, 'password_confirm');
            }
        }

        if (($settings['usercanchangeemail'] and $frm['email'] == '')) {
            array_push($errors, 'email');
        }

        if ($settings['use_user_location']) {
            if ($frm['address'] == '') {
                array_push($errors, 'address');
            }

            if ($frm['city'] == '') {
                array_push($errors, 'city');
            }

            if ($frm['state'] == '') {
                array_push($errors, 'state');
            }

            if ($frm['zip'] == '') {
                array_push($errors, 'zip');
            }

            if ($frm['zip'] == '') {
                array_push($errors, 'country');
            }
        }

        if ($settings['use_transaction_code']) {
            if ($frm['transaction_code'] != '') {
                if ($frm['transaction_code_current'] == $userinfo['transaction_code']) {
                    if (0 < $settings['min_user_password_length']) {
                        if (strlen($frm['transaction_code']) < $settings['min_user_password_length']) {
                            array_push($errors, 'transaction_code_too_small');
                        }
                    }

                    if ($frm['transaction_code'] != $frm['transaction_code2']) {
                        array_push($errors, 'transaction_code_confirm');
                    }
                } else {
                    array_push($errors, 'invalid_transaction_code');
                }
            }

            if ((($frm['transaction_code'] != '' and $frm['password'] != '') and $frm['transaction_code'] == $frm['password'])) {
                array_push($errors, 'transaction_code_vs_password');
            }
        }
    }

    if (sizeof($errors) == 0) {
        if ($settings['account_update_confirmation'] == 1) {
            session_start();
            if ($frm['action2'] == 'confirm') {
                if ($_SESSION['account_update_confirmation_code'] == $frm['account_update_confirmation_code']) {
                    if (is_array($_SESSION['fields'])) {
                        $frm = array_merge($frm, $_SESSION['fields']);
                    } else {
                        header('Location: ?a=edit_account');
                        exit();
                    }
                } else {
                    header('Location: ?a=edit_account&action=confirm&say=invalid_code');
                    exit();
                }
            } else {
                $code = get_rand_md5(50);
                $_SESSION['account_update_confirmation_code'] = $code;
                $_SESSION['fields'] = $frm;
                $info = [];
                $info['confirmation_code'] = $code;
                $info['username'] = $userinfo['username'];
                $info['name'] = $userinfo['name'];
                $info['ip'] = $frm_env['REMOTE_ADDR'];
                send_template_mail('account_update_confirmation', $userinfo['email'], $settings['system_email'], $info);
                header('Location: ?a=edit_account&action=confirm');
                exit();
            }
        }

        $fullname = quote($frm['fullname']);
        $password = quote($frm['password']);
        $enc_password = md5($password);
        $email = quote($frm['email']);

        $egold = quote($frm['egold_account']);
        $perfectmoney = quote($frm['perfectmoney_account']);
        $payeer = quote($frm['payeer_account']);
        $bitcoin = quote($frm['bitcoin_account']);
        $evocash = quote($frm['evocash_account']);
        $intgold = quote($frm['intgold_account']);
        $stormpay = quote($frm['stormpay_account']);
        $ebullion = quote($frm['ebullion_account']);
        $paypal = quote($frm['paypal_account']);
        $goldmoney = quote($frm['goldmoney_account']);
        $eeecurrency = quote($frm['eeecurrency_account']);
        $pecunix = quote($frm['pecunix_account']);

        $address = quote($frm['address']);
        $city = quote($frm['city']);
        $state = quote($frm['state']);
        $zip = quote($frm['zip']);
        $country = quote($frm['country']);
        $transaction_code = quote($frm['transaction_code']);

        if (($userinfo['email'] != $frm['email'] and $settings['usercanchangeemail'] == 1)) {
            $q = 'update hm2_users set email = \''.$email.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        if (($userinfo['egold_account'] != $frm['egold_account'] and $settings['usercanchangeegoldacc'])) {
            $q = 'update hm2_users set egold_account = \''.$egold.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        if (($userinfo['perfectmoney_account'] != $frm['perfectmoney_account'] and $settings['usercanchangeperfectmoneyacc'])) {
            $q = 'update hm2_users set perfectmoney_account = \''.$perfectmoney.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        if (($userinfo['payeer_account'] != $frm['payeer_account'] and $settings['usercanchangepayeeracc'])) {
            $q = 'update hm2_users set payeer_account = \''.$payeer.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        if (($userinfo['bitcoin_account'] != $frm['bitcoin_account'] and $settings['usercanchangebitcoinacc'])) {
            $q = 'update hm2_users set bitcoin_account = \''.$bitcoin.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        if ($frm['password'] != '') {
            $q = 'update hm2_users set password = \''.$enc_password.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
            if ($settings['store_uncrypted_password'] == 1) {
                $pswd = quote($frm['password']);
                $q = 'update hm2_users set pswd = \''.$pswd.'\' where id > 1 and id = '.$userinfo['id'];
                db_query($q);
            }
        }

        if ($frm['transaction_code'] != '') {
            $q = 'update hm2_users set transaction_code = \''.$transaction_code.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        if ($frm['wappassword'] != '') {
            $enc_password = quote(md5($frm['wappassword']));
            $q = 'update hm2_users set stat_password = \''.$enc_password.'\' where id > 1 and id = '.$userinfo['id'];
            db_query($q);
        }

        $edit_location = '';
        if ($settings['use_user_location']) {
            $edit_location = 'address = \''.$address.'\',
                          city = \''.$city.'\',
                          state = \''.$state.'\',
                          zip = \''.$zip.'\',
                          country = \''.$country.'\',
                         ';
        }

        $user_auto_pay_earning = sprintf('%d', $frm['user_auto_pay_earning']);
        $q = 'update hm2_users set name = \''.$fullname.'\',
                                 '.$edit_location.'
    	                         evocash_account = \''.$evocash.'\',
                                 intgold_account = \''.$intgold.'\',
				 stormpay_account = \''.$stormpay.'\',
				 ebullion_account = \''.$ebullion.'\',
				 paypal_account = \''.$paypal.'\',
				 goldmoney_account = \''.$goldmoney.'\',
				 eeecurrency_account = \''.$eeecurrency.'\',
                                 user_auto_pay_earning = '.$user_auto_pay_earning.'
            where id > 1 and id = '.$userinfo['id'];
        db_query($q);
        $q = 'select * from hm2_users where id ='.$userinfo['id'];
        $sth = db_query($q);
        $userinfo = mysql_fetch_array($sth);
        $userinfo['logged'] = 1;

        // 发邮件通知
        if ($settings['sendnotify_when_userinfo_changed'] == 1) {
            $info = [];
            $info['username'] = $userinfo['username'];
            $info['password'] = $password;
            $info['name'] = $userinfo['name'];
            $info['ip'] = $frm_env['REMOTE_ADDR'];
            $info['egold'] = $userinfo['egold_account'];
            $info['perfectmoney'] = $userinfo['perfectmoney_account'];
            $info['email'] = $userinfo['email'];
            if ($frm['email'] == '') {
                $frm['email'] = $userinfo['email'];
            }

            send_template_mail('change_account', $frm['email'], $settings['opt_in_email'], $info);
        }

        header('Location: ?a=edit_account&say=changed');
        exit();
    }
}

include HM_PATH.'/inc/countries.inc';
$q = 'select date_format(\''.$userinfo['date_register'].'\' + interval '.$settings['time_dif'].' day, \'%b-%e-%Y %r\') as date_registered';
$sth = db_query($q);
$row = mysql_fetch_array($sth);
$userinfo['date_register'] = $row['date_registered'];
$smarty->assign('userinfo', $userinfo);
$smarty->assign('errors', $errors);
$smarty->assign('frm', $frm);
$smarty->assign('settings', $settings);
$smarty->assign('countries', $countries);
$smarty->display('edit_account.tpl');
