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
            refresh_theme();
            Cache::forever('last_theme', $theme);
        }

        return $next($request);
    }
}
