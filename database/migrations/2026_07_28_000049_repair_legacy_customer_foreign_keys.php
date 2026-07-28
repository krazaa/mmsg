<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        $this->pointCustomerForeignKeyToUsers('bookings');
        $this->pointCustomerForeignKeyToUsers('payments');
    }

    private function pointCustomerForeignKeyToUsers(string $table): void
    {
        $foreignKeys = DB::select("PRAGMA foreign_key_list('{$table}')");
        $usesLegacyCustomersTable = collect($foreignKeys)->contains(
            fn (object $foreignKey): bool => $foreignKey->from === 'customer_id'
                && $foreignKey->table === 'customers'
        );

        if (! $usesLegacyCustomersTable) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropForeign(['customer_id']);
            $blueprint->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        // This only repairs databases upgraded from the retired customers table.
    }
};
