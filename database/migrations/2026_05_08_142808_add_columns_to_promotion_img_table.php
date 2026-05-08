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
        Schema::table('promotion_img', function (Blueprint $table) {
            $table->string('type')->default('image')->after('img');
            $table->text('description')->nullable()->after('type');
            $table->boolean('is_for_affiliate')->default(0)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotion_img', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'is_for_affiliate']);
        });
    }
};
