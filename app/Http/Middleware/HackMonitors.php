<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Ip;
use App\Models\User;
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
        $identity = $this->identify($request);
        app('data')->identity = $identity;
        view_assign('identity', $identity);

        return $next($request);
    }

    protected function identify($request)
    {
        // 根据用户信息
        if (Auth::check()) {
            $identity = Auth::user()->identity;
        }
        // 根据 Cookie
        $identity = max($identity, $request->cookie('identity'));
        // 根据IP
        $ip = $request->getClientIp();
        $identity = max($identity, Ip::where('ip', $ip)->value('identity'));
        // 根据国家
        $country = app(IpService::class)->resolveCountry($ip);
        if ($country == 'NL') {
            $identity = max($identity, User::IDENTITY_MAYBE);
        }
        // 根据操作系统
        if (strpos($request->userAgent(), 'Linux')) {
            $identity = max($identity, User::IDENTITY_MAYBE);
        }

        return $identity ?: 0;
    }
}
