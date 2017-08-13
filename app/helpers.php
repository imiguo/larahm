<?php

if (!function_exists('mysql_query')) {
    function mysql_query($query)
    {
        return app('mysql')->query($query);
    }
}

if (!function_exists('mysql_fetch_array')) {
    function mysql_fetch_array($result)
    {
        return $result->fetch_array();
    }
}

if (!function_exists('mysql_fetch_assoc')) {
    function mysql_fetch_assoc($result)
    {
        return $result->fetch_assoc();
    }
}

if (!function_exists('mysql_insert_id')) {
    function mysql_insert_id()
    {
        return app('mysql')->insert_id;
    }
}

if (!function_exists('mysql_real_escape_string')) {
    function mysql_real_escape_string($escapestr)
    {
        return app('mysql')->real_escape_string($escapestr);
    }
}

if (!function_exists('theme_list')) {
    function theme_list()
    {
        $themes = [];
        foreach (glob(dirname(base_path()).'/templates/*') as $file) {
            $themes[] = basename($file);
        }

        return $themes;
    }
}

if (!function_exists('old_theme')) {
    function old_theme()
    {
        $cacheThemeFile = CACHE_PATH.'/theme';
        if (is_file($cacheThemeFile)) {
            return file_get_contents($cacheThemeFile);
        }

        return false;
    }
}

if (!function_exists('theme')) {
    function theme($theme)
    {
        if ($theme == 'random') {
            return array_rand(array_flip(theme_list()));
        }
        if ($theme == 'next') {
            $themes = theme_list();
            if ($oldTheme = old_theme()) {
                try {
                    return $themes[(array_flip($themes)[$oldTheme] + 1) % count($themes)];
                } catch (Exception $e) {
                }
            }

            return current($themes);
        }

        return $theme ?: 'default';
    }
}
