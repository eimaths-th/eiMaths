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
        Schema::create('line_crm_leads', function (Blueprint $table) {
            $table->id();

            $table->string('line_user_id')->unique();
            $table->string('display_name')->nullable();

            $table->string('level')->nullable();
            $table->string('interest')->nullable();

            $table->string('step')->default('ask_level');

            $table->string('source')->nullable();
            $table->string('status')->default('new_lead');

            $table->text('last_message')->nullable();

            $table->timestamp('last_interaction_at')->nullable();
            
            $table->integer('lead_score')->default(0);
            $table->timestamp('last_step_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_crm_leads');
    }
};
