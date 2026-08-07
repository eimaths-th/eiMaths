<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('affiliate_payout_accounts')) {
            return;
        }

        Schema::create('affiliate_payout_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('affiliate_profile_id')->constrained('affiliate_profiles', 'id', 'apa_profile_fk')->restrictOnDelete();
            $table->string('payout_type', 16);
            $table->text('details_encrypted');
            $table->string('masked_label', 128);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['affiliate_profile_id', 'payout_type'], 'apa_profile_type_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_payout_accounts');
    }
};
