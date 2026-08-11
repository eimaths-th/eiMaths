<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('affiliate_configurations')
            ->where('key', 'commission_rate_basis_points')
            ->update([
                'value' => '1500',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('affiliate_configurations')
            ->where('key', 'commission_rate_basis_points')
            ->update([
                'value' => '2000',
                'updated_at' => now(),
            ]);
    }
};
