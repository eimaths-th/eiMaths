<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_quizzers', function (Blueprint $table): void {
            $table->string('privacy_notice_version', 50)->nullable()->after('expires_at');
            $table->timestamp('privacy_acknowledged_at')->nullable()->after('privacy_notice_version');
            $table->boolean('marketing_consent')->nullable()->after('privacy_acknowledged_at');
            $table->string('marketing_consent_version', 50)->nullable()->after('marketing_consent');
            $table->timestamp('marketing_consent_recorded_at')->nullable()->after('marketing_consent_version');
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Consent evidence requires a separately reviewed rollback procedure.');
    }
};
