<?php

define('HM_PATH', app_path().'/Hm');

define('CACHE_PATH', storage_path().'/tmpl_c');

if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
    define('HTTPS', true);
} else {
    define('HTTPS', false);
}
