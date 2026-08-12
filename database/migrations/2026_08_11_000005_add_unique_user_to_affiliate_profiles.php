<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'affiliate_profiles';
    private const UNIQUE_INDEX = 'affiliate_profiles_user_id_unique';

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        try {
            $duplicate_owners_exist = DB::table(self::TABLE)
                ->whereNotNull('user_id')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) > 1')
                ->exists();
        } catch (\Throwable $exception) {
            throw new \RuntimeException(
                'Could not verify Affiliate profile ownership before adding the unique index.',
                0,
                $exception
            );
        }

        if ($duplicate_owners_exist) {
            throw new \RuntimeException(
                'Cannot add the Affiliate profile ownership index because duplicate owners exist.'
            );
        }

        if (!Schema::hasIndex(self::TABLE, self::UNIQUE_INDEX, 'unique')) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->unique('user_id', self::UNIQUE_INDEX);
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        if (Schema::hasIndex(self::TABLE, self::UNIQUE_INDEX, 'unique')) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->dropUnique(self::UNIQUE_INDEX);
            });
        }
    }
};
