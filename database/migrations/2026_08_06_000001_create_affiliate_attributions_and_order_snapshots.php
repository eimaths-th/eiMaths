<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('affiliate_attributions')) {
            Schema::create('affiliate_attributions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('affiliate_profile_id')->constrained('affiliate_profiles')->restrictOnDelete();
                $table->foreignId('contact_id')->constrained('contacts')->restrictOnDelete();
                $table->dateTime('captured_at');
                $table->dateTime('expires_at');
                $table->dateTime('ended_at')->nullable();
                $table->string('source_context', 64);
                $table->timestamps();
                $table->index(['contact_id', 'expires_at']);
                $table->index(['affiliate_profile_id', 'captured_at']);
            });
        }

        if (Schema::hasTable('contacts') && !Schema::hasColumn('contacts', 'current_affiliate_attribution_id')) {
            Schema::table('contacts', function (Blueprint $table): void {
                $table->foreignId('current_affiliate_attribution_id')
                    ->nullable()
                    ->after('updated_by')
                    ->constrained('affiliate_attributions')
                    ->restrictOnDelete();
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table): void {
                if (!Schema::hasColumn('orders', 'affiliate_attribution_id')) {
                    $table->foreignId('affiliate_attribution_id')
                        ->nullable()
                        ->after('courses_pending_id')
                        ->constrained('affiliate_attributions')
                        ->restrictOnDelete();
                }
                if (!Schema::hasColumn('orders', 'affiliate_commission_rate_basis_points')) {
                    $table->unsignedInteger('affiliate_commission_rate_basis_points')->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_attributions');

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table): void {
                if (Schema::hasColumn('orders', 'affiliate_attribution_id')) {
                    $table->dropForeign(['affiliate_attribution_id']);
                    $table->dropColumn('affiliate_attribution_id');
                }
                if (Schema::hasColumn('orders', 'affiliate_commission_rate_basis_points')) {
                    $table->dropColumn('affiliate_commission_rate_basis_points');
                }
            });
        }

        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'current_affiliate_attribution_id')) {
            Schema::table('contacts', function (Blueprint $table): void {
                $table->dropForeign(['current_affiliate_attribution_id']);
                $table->dropColumn('current_affiliate_attribution_id');
            });
        }
    }
};
