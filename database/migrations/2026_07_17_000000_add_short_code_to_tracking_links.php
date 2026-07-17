<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracking_links', function (Blueprint $table) {
            $table->string('short_code', 16)->nullable()->unique()->after('tracking_code');
        });

        DB::table('tracking_links')
            ->whereNull('short_code')
            ->orderBy('id')
            ->each(function ($link): void {
                do {
                    $shortCode = Str::lower(Str::random(8));
                } while (DB::table('tracking_links')->where('short_code', $shortCode)->exists());

                DB::table('tracking_links')
                    ->where('id', $link->id)
                    ->update(['short_code' => $shortCode]);
            });
    }

    public function down(): void
    {
        Schema::table('tracking_links', function (Blueprint $table) {
            $table->dropUnique('tracking_links_short_code_unique');
            $table->dropColumn('short_code');
        });
    }
};
