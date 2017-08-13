<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require app_path('Hm').'/../common/function.php';

if (!validate()) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Invalid request.';
    exit;
}

include app_path('Hm').'/lib/config.inc.php';
ini_set('display_errors', '1');

function mail_test()
{
    $info['username'] = 'entimm';
    $info['password'] = '********';
    $info['name'] = 'enjoy';
    $info['email'] = '1194316669@qq.com';
    $ret = send_template_mail('registration', $info['email'], 'midollaradm@gmail.com', $info);
}

function testErrorHandler($errno, $errstr, $errfile, $errline)
{
    // var_dump(func_get_args());
    if (!(error_reporting() & $errno)) {
        return false;
    }

    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ', PHP '.PHP_VERSION.' ('.PHP_OS.")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
        break;

    default:
        echo "Unknown error type: [$errno] $errstr<br />\n";
        break;
    }

    return true;
}

function error_handler_test()
{
    set_error_handler('testErrorHandler');
}

function deposit_test()
{
    add_deposit(3, 46, 1000, time(), 'u1888888', 1, 0);
}

// deposit_test();
// mail_test();
// phpinfo();
echo 'ok';
