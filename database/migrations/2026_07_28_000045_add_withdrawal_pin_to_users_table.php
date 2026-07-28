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
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'withdrawal_pin')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('withdrawal_pin');
            });
        }
    }
};
