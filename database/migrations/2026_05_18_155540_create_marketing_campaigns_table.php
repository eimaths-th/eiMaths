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
        Schema::create('marketing_campaigns', function (Blueprint $table) {

            $table->id();

            // campaign info
            $table->string('title');

            $table->longText('message');

            $table->string('image_url')->nullable();

            // segmentation
            $table->string('segment_level')->nullable();
            $table->string('segment_interest')->nullable();
            $table->string('segment_status')->nullable();

            // scheduling
            $table->timestamp('schedule_at')->nullable();

            // campaign status
            $table->enum('status', [
                'draft',
                'scheduled',
                'processing',
                'completed',
                'cancelled',
            ])->default('draft');

            // statistics
            $table->integer('target_count')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);

            // created by
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
    }
};
