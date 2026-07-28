<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('plot_packages', 'balloon_payments')) {
            Schema::table('plot_packages', function (Blueprint $table): void {
                $table->json('balloon_payments')->nullable()->after('month_36_balloon');
            });
        }

        DB::table('plot_packages')
            ->whereNull('balloon_payments')
            ->orderBy('id')
            ->each(function (object $package): void {
                $payments = collect([
                    ['month' => 12, 'amount' => (float) $package->month_12_balloon],
                    ['month' => 24, 'amount' => (float) $package->month_24_balloon],
                    ['month' => 36, 'amount' => (float) $package->month_36_balloon],
                ])->filter(fn (array $payment): bool => $payment['amount'] > 0)->values();

                DB::table('plot_packages')->where('id', $package->id)->update([
                    'balloon_payments' => $payments->isEmpty() ? null : $payments->toJson(),
                ]);
            });
    }

    public function down(): void
    {
        // Compatibility repair for packages created before flexible balloons existed.
    }
};
