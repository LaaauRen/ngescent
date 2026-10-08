<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        abort_unless($user && $user->isAdmin(), 403, 'Akses terbatas: hanya untuk Admin Toko.');
        return $next($request);
    }
}
