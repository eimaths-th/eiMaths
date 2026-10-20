<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('faq_daily_reads', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('faq_reference_id');
            $table->date('read_date');
            $table->unsignedBigInteger('read_count')->default(0);
            $table->string('question_th');
            $table->string('question_en')->nullable();
            $table->string('category_th');
            $table->string('category_en')->nullable();
            $table->unique(['faq_reference_id', 'read_date'], 'faq_reads_faq_date_unique');
            $table->index(['read_date', 'faq_reference_id'], 'faq_reads_date_faq_index');
        });
        Schema::create('faq_read_receipts', function (Blueprint $table): void {
            $table->char('event_key', 64)->primary();
            $table->foreignId('daily_read_id')->constrained('faq_daily_reads')->restrictOnDelete();
            $table->dateTime('first_recorded_at', 6);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('faq_read_receipts');
        Schema::dropIfExists('faq_daily_reads');
    }
};
