<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('withdrawal_pin_failed_attempts')->default(0)->after('withdrawal_pin');
            $table->timestamp('withdrawal_pin_locked_until')->nullable()->after('withdrawal_pin_failed_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['withdrawal_pin_failed_attempts', 'withdrawal_pin_locked_until']);
        });
    }
};
