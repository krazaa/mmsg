<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectCustomerManagementAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role === 'customer') {
            abort(403);
        }

        return $next($request);
    }
}
