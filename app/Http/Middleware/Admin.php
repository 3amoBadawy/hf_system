<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class Admin{
    public function handle(Request $request, Closure $next){
        $u = Auth::user();
        if ($u && ((int)($u->is_admin ?? 0) === 1)) return $next($request);
        abort(403,'Unauthorized.');
    }
}
