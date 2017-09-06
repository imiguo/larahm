<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Visitlog;

class EndMiddleware
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
        return $next($request);
    }

    public function terminate($request, $response)
    {
        Visitlog::create([
            'ip' => $request->getClientIp(),
            'time' => time(),
            'url' => $request->url(),
            'username' => auth()->check() ? auth()->user()->username : '',
            'data' => $request->all(),
            'agent' => $request->userAgent(),
        ]);
    }
}
