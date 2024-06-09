<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Cookie;

class SetUserCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->cookie('todolist')) {
            $cookieVal = (string) Str::random(8);
            $cookie = Cookie::create(['cookie_val' => $cookieVal]);
            $request->merge(['todolist' => $cookieVal]);
            return $next($request)->withCookie(cookie('todolist', $cookieVal, 60*24*365));
        }
        $request->merge(['todolist' => $request->cookie('todolist')]);
        return $next($request);
    }
}
