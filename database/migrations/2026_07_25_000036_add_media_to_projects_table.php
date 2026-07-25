<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('location');
            $table->string('blueprint_path')->nullable()->after('image_path');
        });

        DB::table('projects')
            ->where('slug', 'abdullah-town')
            ->update([
                'image_path' => 'images/projects/abdullah-town.jpg',
                'blueprint_path' => 'images/projects/abdullah-town-blueprint.jpg',
            ]);
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'blueprint_path']);
        });
    }
};
