<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class Admin{
    public function handle(Request $request, Closure $next){
        $u = Auth::user();
        if ($u && (property_exists($u,'is_admin') ? $u->is_admin : false)) {
            return $next($request);
        }
        abort(403,'Unauthorized.');
    }
}
