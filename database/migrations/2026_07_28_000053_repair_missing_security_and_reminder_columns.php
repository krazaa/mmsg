<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'withdrawal_pin')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('withdrawal_pin')->nullable()->after('withdrawal_frequency');
            });
        }

        if (! Schema::hasColumn('installment_schedules', 'reminder_sent_at')) {
            Schema::table('installment_schedules', function (Blueprint $table): void {
                $table->timestamp('reminder_sent_at')->nullable()->after('status');
                $table->index(
                    ['due_date', 'status', 'reminder_sent_at'],
                    'installments_reminder_lookup'
                );
            });
        }
    }

    public function down(): void
    {
        // Compatibility repair; these columns are part of the canonical schema.
    }
};
