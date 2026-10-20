<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('faq_localized_slug_states', function (Blueprint $table): void {
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->unsignedBigInteger('last_revision');
            $table->string('source_base', 120);
            $table->dateTime('initialized_at', 6);
            $table->primary(['faq_id', 'locale'], 'faq_localized_state_primary');
        });
        Schema::create('faq_slug_routes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('slug', 255);
            $table->unsignedBigInteger('revision')->nullable();
            $table->unsignedTinyInteger('current_marker')->nullable();
            $table->dateTime('retired_at', 6)->nullable();
            $table->dateTime('expires_at', 6)->nullable();
            $table->unique(['locale', 'slug'], 'faq_route_identity_unique');
            $table->unique(['faq_id', 'locale', 'current_marker'], 'faq_current_route_unique');
            $table->index(['expires_at', 'id'], 'faq_route_expiry_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_slug_routes');
        Schema::dropIfExists('faq_localized_slug_states');
    }
};
