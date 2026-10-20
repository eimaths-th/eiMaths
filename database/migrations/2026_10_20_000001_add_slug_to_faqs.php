<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table): void {
            $table->string('slug', 255)->nullable()->unique('faqs_slug_unique');
        });
    }
    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table): void {
            $table->dropUnique('faqs_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
