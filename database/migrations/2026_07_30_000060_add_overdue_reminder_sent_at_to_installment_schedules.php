<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_schedules', function (Blueprint $table) {
            $table->timestamp('overdue_reminder_sent_at')->nullable()->after('reminder_sent_at');
            $table->index(
                ['due_date', 'status', 'overdue_reminder_sent_at'],
                'installments_overdue_reminder_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::table('installment_schedules', function (Blueprint $table) {
            $table->dropIndex('installments_overdue_reminder_lookup');
            $table->dropColumn('overdue_reminder_sent_at');
        });
    }
};
