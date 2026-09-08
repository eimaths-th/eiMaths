<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $duplicate = DB::table('departments')->whereNotNull('code')->selectRaw('UPPER(TRIM(code)) AS normalized_code')
            ->groupByRaw('UPPER(TRIM(code))')->havingRaw('COUNT(*) > 1')->exists();
        if ($duplicate) {
            throw new RuntimeException('Duplicate branch codes: resolve the branch:audit findings before this migration. No branch has been renamed.');
        }
        Schema::table('departments', fn (Blueprint $table) => $table->unique('code', 'departments_code_unique'));
    }

    public function down(): void
    {
        Schema::table('departments', fn (Blueprint $table) => $table->dropUnique('departments_code_unique'));
    }
};
