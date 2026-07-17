<?php

use Illuminate\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracking_link_id')
                ->constrained('tracking_links')
                ->cascadeOnDelete();
            $table->string('ip_hash', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->text('landing_page')->nullable();
            $table->timestamps();

            $table->index(['tracking_link_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_link_clicks');
    }
};
