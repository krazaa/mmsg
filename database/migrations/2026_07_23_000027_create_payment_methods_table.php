<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->boolean('customer_portal')->default(false)->index();
            $table->boolean('status')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('payment_methods')->insert([
            ['code' => 'online_transfer', 'name' => 'Online Transfer', 'customer_portal' => true, 'status' => true, 'sort_order' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'direct_deposit', 'name' => 'Direct Bank Deposit', 'customer_portal' => true, 'status' => true, 'sort_order' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'crypto', 'name' => 'Crypto', 'customer_portal' => true, 'status' => true, 'sort_order' => 30, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
