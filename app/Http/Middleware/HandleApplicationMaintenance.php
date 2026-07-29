<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class HandleApplicationMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAlwaysAvailable($request) || $this->isManagementUser($request)) {
            return $next($request);
        }

        try {
            if (! SiteSetting::maintenanceModeEnabled()) {
                return $next($request);
            }
        } catch (Throwable) {
            return $next($request);
        }

        return response()
            ->view('maintenance', SiteSetting::maintenancePage(), Response::HTTP_SERVICE_UNAVAILABLE)
            ->header('Retry-After', '3600');
    }

    private function isManagementUser(Request $request): bool
    {
        return in_array($request->user()?->role, ['super_admin', 'admin'], true);
    }

    private function isAlwaysAvailable(Request $request): bool
    {
        return $request->is('up') || $request->is('management/login');
    }
}
