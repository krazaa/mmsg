<?php

namespace App\Providers;

use App\Contracts\ActivityLogReadRepository;
use App\Contracts\BookingCreator;
use App\Contracts\BookingLifecycleManager;
use App\Contracts\BookingPaymentRecorder;
use App\Contracts\CommissionDistributor;
use App\Contracts\InstallmentScheduleGenerator;
use App\Models\User;
use App\Repositories\EloquentActivityLogReadRepository;
use App\Services\BookingLifecycleService;
use App\Services\BookingService;
use App\Services\CommissionService;
use App\Services\InstallmentScheduleService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Passkeys;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ActivityLogReadRepository::class, EloquentActivityLogReadRepository::class);
        $this->app->bind(BookingCreator::class, BookingService::class);
        $this->app->bind(BookingLifecycleManager::class, BookingLifecycleService::class);
        $this->app->bind(BookingPaymentRecorder::class, BookingService::class);
        $this->app->bind(CommissionDistributor::class, CommissionService::class);
        $this->app->bind(InstallmentScheduleGenerator::class, InstallmentScheduleService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passkeys::authorizeLoginUsing(
            fn ($request, PasskeyUser $user, Passkey $passkey): bool => $user instanceof User
                && $user->status
                && $user->hasVerifiedEmail()
        );

        User::saved(function (User $account): void {
            if (! Schema::hasTable(config('permission.table_names.roles')) || ! Role::where('name', $account->role)->exists()) {
                return;
            }

            $user = $account::class === User::class ? $account : User::find($account->id);
            if ($user && ! $user->hasExactRoles($account->role)) {
                $user->syncRoles($account->role);
            }
        });
    }
}
