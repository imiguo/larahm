<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class ChangeTheme
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $theme = theme();
        config(['hm.theme' => $theme]);
        if ($theme != Cache::get('last_theme', false)) {
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
            Cache::forever('last_theme', $theme);
        }

        return $next($request);
    }
}
