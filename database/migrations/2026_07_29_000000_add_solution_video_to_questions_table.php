<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('answer_explanation_video_url')
                ->nullable()
                ->after('answer_explanation_image');
            $table->string('answer_explanation_video_provider', 50)
                ->nullable()
                ->after('answer_explanation_video_url');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'answer_explanation_video_url',
                'answer_explanation_video_provider',
            ]);
        });
    }
};
