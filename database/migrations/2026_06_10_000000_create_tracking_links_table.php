<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_links', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('base_url');
            $table->string('utm_source');
            $table->string('utm_medium');
            $table->string('utm_campaign');
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->text('generated_url');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('utm_source');
            $table->index('utm_campaign');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_links');
    }
};
