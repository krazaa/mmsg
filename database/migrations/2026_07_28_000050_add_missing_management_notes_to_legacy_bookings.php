<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bookings', 'management_notes')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->text('management_notes')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        // Compatibility repair for databases created before this field existed.
    }
};
