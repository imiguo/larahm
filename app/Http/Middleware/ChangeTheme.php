<?php

namespace App\Http\Middleware;

use Closure;

class ChangeTheme
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        define('THEME', theme(env('THEME')));

        define('TMPL_PATH', dirname(base_path()).'/templates/'.THEME.'/tmpl');

        $cacheThemeFile = CACHE_PATH.'/theme';
        if (!is_file($cacheThemeFile) || THEME != file_get_contents($cacheThemeFile)) {
            foreach (glob(public_path().'/*') as $file) {
                if (strpos($file, 'index.php') !== false) {
                    continue;
                }
                unlink($file);
            }
            foreach (glob(dirname(TMPL_PATH).'/public/*') as $file) {
                $target = public_path().'/'.basename($file);
                symlink($file, $target);
            }
            file_put_contents($cacheThemeFile, THEME);
        }

        return $next($request);
    }
}
