<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->string('slug')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'position']);
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('faq_categories')
                ->restrictOnDelete();
            $table->string('question_en')->nullable()->after('question');
            $table->text('answer_en')->nullable()->after('answer');
            $table->unsignedInteger('source_reference')->nullable()->unique()->after('answer_en');
            $table->text('selection_reason')->nullable()->after('source_reference');
            $table->text('publication_note')->nullable()->after('selection_reason');
        });

        $now = now();
        $general_category_id = DB::table('faq_categories')->insertGetId([
            'name' => 'ทั่วไป',
            'name_en' => 'General',
            'description' => 'คำถามทั่วไปเกี่ยวกับ eiMaths',
            'description_en' => 'General questions about eiMaths',
            'slug' => 'general',
            'position' => 0,
            'is_published' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('faqs')->whereNull('category_id')->update([
            'category_id' => $general_category_id,
        ]);
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn([
                'question_en',
                'answer_en',
                'source_reference',
                'selection_reason',
                'publication_note',
            ]);
        });

        Schema::dropIfExists('faq_categories');
    }
};
