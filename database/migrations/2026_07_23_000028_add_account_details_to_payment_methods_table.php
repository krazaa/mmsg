<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('bank_name', 150)->nullable()->after('name');
            $table->string('account_title', 150)->nullable()->after('bank_name');
            $table->string('account_number', 150)->nullable()->after('account_title');
            $table->string('crypto_network', 100)->nullable()->after('account_number');
            $table->string('wallet_address', 255)->nullable()->after('crypto_network');
            $table->text('instructions')->nullable()->after('wallet_address');
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'account_title', 'account_number', 'crypto_network', 'wallet_address', 'instructions']);
        });
    }
};
