<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'projects',
        'blocks',
        'plots',
        'plot_packages',
        'bookings',
        'installment_schedules',
        'payments',
        'referrals',
        'commission_rules',
        'commission_payouts',
        'commissions',
        'plot_allotments',
        'payment_methods',
        'email_campaigns',
        'email_campaign_recipients',
        'email_unsubscribes',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn($tableName, 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'updated_by')) {
                    $table->dropConstrainedForeignId('updated_by');
                }
                if ($tableName !== 'email_campaigns' && Schema::hasColumn($tableName, 'created_by')) {
                    $table->dropConstrainedForeignId('created_by');
                }
            });
        }
    }
};
