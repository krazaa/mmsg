<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->json('filters')->nullable();
            $table->string('status', 30)->default('queued')->index();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('email_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->uuid('unsubscribe_token')->unique();
            $table->string('status', 30)->default('queued')->index();
            $table->timestamp('dispatched_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            $table->unique(['email_campaign_id', 'email']);
        });

        Schema::create('email_unsubscribes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('unsubscribed_at');
            $table->string('source')->default('campaign');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_unsubscribes');
        Schema::dropIfExists('email_campaign_recipients');
        Schema::dropIfExists('email_campaigns');
    }
};
