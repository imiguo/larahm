<?php

use App\Exceptions\HmException;
use Illuminate\Filesystem\Filesystem;

if (! function_exists('mysql_query')) {
    function mysql_query($query)
    {
        return app('mysql')->query($query);
    }
}

if (! function_exists('mysql_fetch_array')) {
    function mysql_fetch_array($result)
    {
        return $result->fetch_array();
    }
}

if (! function_exists('mysql_fetch_assoc')) {
    function mysql_fetch_assoc($result)
    {
        return $result->fetch_assoc();
    }
}

if (! function_exists('mysql_insert_id')) {
    function mysql_insert_id()
    {
        return app('mysql')->insert_id;
    }
}

if (! function_exists('mysql_real_escape_string')) {
    function mysql_real_escape_string($escapestr)
    {
        return app('mysql')->real_escape_string($escapestr);
    }
}

if (! function_exists('theme_list')) {
    function theme_list()
    {
        $themes = [];
        foreach (glob(dirname(base_path()).'/templates/*') as $file) {
            if (is_dir($file) && ($name = basename($file)) != 'vendor') {
                $themes[] = $name;
            }
        }

        return $themes;
    }
}

if (! function_exists('old_theme')) {
    function old_theme()
    {
        return cache('last_theme');
    }
}

if (! function_exists('theme')) {
    function theme()
    {
        $theme = config('hm.theme');
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

if (! function_exists('refresh_theme')) {
    function refresh_theme()
    {
        foreach (glob(public_path().'/*') as $file) {
            if (strpos($file, 'index.php') !== false) {
                continue;
            }
            unlink($file);
        }
        foreach (glob(dirname(tmpl_path()).'/public/*') as $file) {
            $target = public_path().'/'.basename($file);
            symlink($file, $target);
        }
    }
}

if (! function_exists('tmpl_path')) {
    function tmpl_path()
    {
        return dirname(base_path()).'/templates/'.config('hm.theme').'/tmpl';
    }
}

if (! function_exists('hanlder_app')) {
    function hanlder_app($app_file)
    {
        ob_start();
        try {
            include $app_file;
        } catch (HmException $e) {
            $httpReturn = $e->resolveResponse();
        }
        $html = ob_get_clean();

        return $httpReturn ?? $html;
    }
}

if (! function_exists('is_production')) {
    function is_production()
    {
        return config('app.env') == 'production';
    }
}

if (! function_exists('view_assign')) {
    function view_assign($key, $value)
    {
        app('view_data')->put($key, $value);
    }
}

if (! function_exists('view_execute')) {
    function view_execute($view)
    {
        $view_data = [
            'tag' => crc32(config('hm.theme')),
            'csrf_token' => csrf_token(),
            'app_name' => env('APP_NAME'),
            'app_full_name' => env('APP_FULL_NAME'),
            'app_site' => env('APP_SITE'),
            'app_url' => env('APP_URL'),
        ];
        $view_data = array_merge($view_data, app('view_data')->all());

        app('smarty')->assign($view_data);
        $html = app('smarty')->fetch($view);
        echo config('hm.auto_blade') ? blade_string($html, $view_data) : $html;
    }
}

if (! function_exists('blade')) {
    function blade($view, $data = [])
    {
        echo view($view, $data)->render();
    }
}

if (! function_exists('blade_string')) {
    function blade_string($string, $data = [])
    {
        $name = sha1($string);
        $filesystem = app(Filesystem::class);
        if (! view()->exists("_{$name}")) {
            $filesystem->put(config('hm.blade_path')."/_{$name}.blade.php", $string);
        }

        return view("_{$name}", $data)->render();
    }
}

if (! function_exists('smarty_blade_block')) {
    function smarty_blade_block($params, $content, $smarty, &$repeat, $template)
    {
        if (! $repeat && $content) {
            return blade_string($content, $params);
        }
    }
}
