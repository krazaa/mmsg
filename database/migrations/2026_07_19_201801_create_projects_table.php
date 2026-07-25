<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('location')->nullable();

            // 400 kanal = 8,000 marla
            $table->decimal('gross_area_marla', 14, 2);
            $table->decimal('saleable_area_marla', 14, 2);
            $table->decimal('reserved_area_marla', 14, 2)->default(0);
            $table->decimal('sold_area_marla', 14, 2)->default(0);

            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name');
            $table->decimal('total_area_marla', 14, 2);
            $table->decimal('saleable_area_marla', 14, 2);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'name']);
            $table->index(['project_id', 'status']);
        });

        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('block_id')
                ->constrained('blocks')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('plot_number', 50);
            $table->decimal('size_marla', 10, 2);
            $table->string('category', 50);
            $table->decimal('base_price', 15, 2);
            $table->decimal('premium_amount', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2);
            $table->string('status', 30)->default('available');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['block_id', 'plot_number'], 'plots_block_plot_number_unique');
            $table->index(['project_id', 'status']);
            $table->index(['block_id', 'status']);
            $table->index(['category', 'size_marla']);
        });

        Schema::create('plot_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->decimal('size_marla', 10, 2);
            $table->decimal('booking_amount', 15, 2)->default(350000);
            $table->unsignedSmallInteger('months')->default(36);
            $table->decimal('monthly_amount', 15, 2)->default(50000);
            $table->decimal('month_12_balloon', 15, 2)->default(150000);
            $table->decimal('month_24_balloon', 15, 2)->default(250000);
            $table->decimal('month_36_balloon', 15, 2)->default(350000);
            $table->json('balloon_payments')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['project_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_packages');
        Schema::dropIfExists('plots');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('projects');
    }
};
