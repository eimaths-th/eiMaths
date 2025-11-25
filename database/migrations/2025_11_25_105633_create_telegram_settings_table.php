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
        Schema::create('telegram_settings', function (Blueprint $table) {
            $table->id();
            $table->text('bot_token')->comment('Encrypted bot token');
            $table->text('chat_id')->comment('Encrypted chat ID');
            $table->boolean('status')->default(1)->comment('1=active, 0=inactive');
            $table->string('description')->nullable()->comment('Description for this setting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_settings');
    }
};
