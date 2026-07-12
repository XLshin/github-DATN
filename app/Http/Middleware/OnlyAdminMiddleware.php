<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlyAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->isAdmin()) {
            abort(403, 'Chỉ quản trị viên mới được thực hiện chức năng này.');
        }

        return $next($request);
    }
}
