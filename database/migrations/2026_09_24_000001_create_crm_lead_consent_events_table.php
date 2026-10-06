<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crm_lead_consent_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lead_id')->constrained('crm_leads')->cascadeOnDelete();
            $table->boolean('marketing_consent');
            $table->timestamp('recorded_at');
            $table->json('context');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Consent event history requires a separately reviewed rollback procedure.');
    }
};
