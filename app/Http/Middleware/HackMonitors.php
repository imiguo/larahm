<?php

namespace App\Http\Middleware;

use App\Models\Ip;
use Closure;
use App\Services\IpService;
use Illuminate\Support\Facades\Auth;

class HackMonitors
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
        $is_monitor = $this->is_monitor($request);
        app('data')->is_monitor = $is_monitor;
        view_assign('is_monitor', $is_monitor);

        return $next($request);
    }

    protected function is_monitor($request)
    {
        $ip = $request->getClientIp();
        $is_monitor = Ip::where('ip', ip2long($ip))->value('is_monitor');
        if ($is_monitor) {
            return true;
        }
        $country = app(IpService::class)->resolveCountry($ip);
        if ($country == 'NL') {
            return true;
        }
        if (strpos($request->userAgent(), 'Linux')) {
            return true;
        }
        if ($request->cookie('identity') == 'monitor') {
            return true;
        }
        if (Auth::check() && Auth::user()->identity == 'monitor') {
            return true;
        }

        return false;
    }
}
