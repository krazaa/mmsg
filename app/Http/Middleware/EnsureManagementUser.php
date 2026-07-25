<?php

namespace App\Http\Middleware;

use App\Support\Permissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureManagementUser
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->can(Permissions::ACCESS_MANAGEMENT), 403);

        return $next($request);
    }
}
