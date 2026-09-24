<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table): void {
            $table->boolean('marketing_consent')->nullable();
            $table->timestamp('marketing_consent_recorded_at')->nullable();
            $table->json('marketing_consent_context')->nullable();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Consent evidence requires a separately reviewed rollback procedure.');
    }
};
