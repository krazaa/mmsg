<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_settings', function (Blueprint $table) {
            $table->id();
            $table->string('frequency', 10)->unique();
            $table->unsignedInteger('request_limit')->default(1);
            $table->decimal('minimum_amount', 15, 2)->default(1);
            $table->decimal('maximum_amount', 15, 2)->default(0);
            $table->timestamps();
        });

        foreach (['daily', 'weekly', 'monthly'] as $frequency) {
            DB::table('withdrawal_settings')->insert([
                'frequency' => $frequency,
                'request_limit' => $this->setting("withdrawal_{$frequency}_request_limit", '1'),
                'minimum_amount' => $this->setting("withdrawal_{$frequency}_minimum_amount", '1'),
                'maximum_amount' => $this->setting("withdrawal_{$frequency}_maximum_amount", '0'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_settings');
    }

    private function setting(string $key, string $default): string
    {
        return (string) (DB::table('site_settings')->where('key', $key)->value('value') ?? $default);
    }
};
