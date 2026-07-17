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
            $table->string('tracking_code', 40)->nullable()->unique()->after('id');
            $table->string('created_by_name')->nullable()->after('created_by');
            $table->softDeletes();
        });

        DB::table('tracking_links')
            ->orderBy('id')
            ->each(function ($link) {
                $trackingCode = Str::lower(Str::random(32));
                $separator = str_contains($link->generated_url, '?') ? '&' : '?';
                $creatorName = $link->created_by
                    ? DB::table('users')->where('id', $link->created_by)->value('name')
                    : null;

                DB::table('tracking_links')
                    ->where('id', $link->id)
                    ->update([
                        'tracking_code' => $trackingCode,
                        'created_by_name' => $creatorName,
                        'generated_url' => rtrim($link->generated_url, '?&')
                            . $separator
                            . 'tracking_code=' . rawurlencode($trackingCode),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('tracking_links', function (Blueprint $table) {
            $table->dropUnique('tracking_links_tracking_code_unique');
            $table->dropSoftDeletes();
            $table->dropColumn(['tracking_code', 'created_by_name']);
        });
    }
};
