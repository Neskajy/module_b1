<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) {
            return back()->withErrors(["error" => "Вы не авторизованы"]);
        } else if ($user->role !== "admin") {
            back()->withErrors(["error" => "Вы не админ"]);
        }
        return $next($request);
    }
}
