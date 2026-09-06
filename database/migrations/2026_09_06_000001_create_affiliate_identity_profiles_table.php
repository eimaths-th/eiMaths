<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_identity_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('affiliate_profile_id')->unique()->constrained('affiliate_profiles')->cascadeOnDelete();
            $table->string('honorific', 16)->nullable();
            $table->string('legal_first_name', 100)->nullable();
            $table->string('legal_last_name', 100)->nullable();
            $table->text('national_id')->nullable();
            $table->char('national_id_hash', 64)->nullable()->unique();
            $table->date('card_expires_on')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('subdistrict', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 5)->nullable();
            $table->string('id_document_path')->nullable();
            $table->string('id_document_mime', 100)->nullable();
            $table->unsignedBigInteger('id_document_size')->nullable();
            $table->string('status', 32)->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->string('terms_version', 32);
            $table->dateTime('terms_accepted_at');
            $table->string('privacy_version', 32);
            $table->dateTime('privacy_acknowledged_at');
            $table->boolean('marketing_consent')->default(false);
            $table->timestamp('marketing_consent_at')->nullable();
            $table->char('registration_ip_hash', 64)->nullable();
            $table->string('registration_user_agent', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_identity_profiles');
    }
};
