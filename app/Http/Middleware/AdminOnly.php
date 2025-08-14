<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) abort(401);

        $ok = false;

        $allowedUsernames = collect(explode(',', (string) env('ADMIN_USERNAMES', 'khaled,admin')))
            ->map(fn($v)=>trim($v))->filter()->all();
        $allowedEmails = collect(explode(',', (string) env('ADMIN_EMAILS', 'khaled@high-furniture.com')))
            ->map(fn($v)=>trim($v))->filter()->all();

        if (!empty($user->username) && in_array($user->username, $allowedUsernames, true)) $ok = true;
        if (!empty($user->email)    && in_array($user->email,    $allowedEmails,    true)) $ok = true;

        if (!$ok) abort(403, 'Admins only.');
        return $next($request);
    }
}
