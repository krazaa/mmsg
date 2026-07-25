<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payment_methods')
            ->where('code', 'online_transfer')
            ->where('account_number', 'PK12 MEZN 0001 2345 6789 0123')
            ->update([
                'bank_name' => null,
                'account_title' => null,
                'account_number' => null,
                'instructions' => null,
                'customer_portal' => false,
                'status' => false,
            ]);

        DB::table('payment_methods')
            ->where('code', 'direct_deposit')
            ->where('account_number', '0123-456789-01')
            ->update([
                'bank_name' => null,
                'account_title' => null,
                'account_number' => null,
                'instructions' => null,
                'customer_portal' => false,
                'status' => false,
            ]);

        DB::table('payment_methods')
            ->where('code', 'crypto')
            ->where('wallet_address', 'TDemoAbdullahTownWalletAddress123456789')
            ->update([
                'bank_name' => null,
                'crypto_network' => null,
                'wallet_address' => null,
                'instructions' => null,
                'customer_portal' => false,
                'status' => false,
            ]);
    }

    public function down(): void
    {
        // Intentionally irreversible: production must never restore placeholder account details.
    }
};
