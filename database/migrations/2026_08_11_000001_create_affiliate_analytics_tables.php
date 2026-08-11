<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_click_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('affiliate_profile_id')
                ->constrained('affiliate_profiles', 'id', 'affiliate_click_events_profile_fk')
                ->restrictOnDelete();
            $table->foreignId('affiliate_content_id')
                ->nullable()
                ->constrained('affiliate_contents', 'id', 'affiliate_click_events_content_fk')
                ->nullOnDelete();
            $table->string('campaign_code', 96)->nullable();
            $table->string('source_type', 16);
            $table->string('destination_target', 2048);
            $table->char('visitor_hash', 64);
            $table->char('unique_window_key', 26);
            $table->boolean('is_unique')->default(false);
            $table->dateTime('clicked_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['affiliate_profile_id', 'clicked_at'], 'affiliate_click_events_profile_clicked_idx');
            $table->index(['affiliate_profile_id', 'is_unique', 'clicked_at'], 'affiliate_click_events_profile_unique_clicked_idx');
            $table->index(['affiliate_profile_id', 'campaign_code', 'clicked_at'], 'affiliate_click_events_profile_campaign_clicked_idx');
            $table->index(['affiliate_profile_id', 'visitor_hash', 'clicked_at'], 'affiliate_click_events_profile_visitor_clicked_idx');
            $table->index('unique_window_key', 'affiliate_click_events_window_idx');
        });

        Schema::create('affiliate_visitor_windows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('affiliate_profile_id')
                ->constrained('affiliate_profiles', 'id', 'affiliate_visitor_windows_profile_fk')
                ->cascadeOnDelete();
            $table->char('visitor_hash', 64);
            $table->char('unique_window_key', 26);
            $table->dateTime('last_unique_at');
            $table->dateTime('last_seen_at');
            $table->timestamps();

            $table->unique(['affiliate_profile_id', 'visitor_hash'], 'affiliate_visitor_windows_profile_visitor_unique');
        });

        Schema::create('affiliate_click_daily_metrics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('affiliate_profile_id')
                ->constrained('affiliate_profiles', 'id', 'affiliate_click_daily_metrics_profile_fk')
                ->restrictOnDelete();
            $table->date('metric_date');
            $table->string('dimension_key', 128);
            $table->foreignId('affiliate_content_id')
                ->nullable()
                ->constrained('affiliate_contents', 'id', 'affiliate_click_daily_metrics_content_fk')
                ->nullOnDelete();
            $table->string('campaign_code', 96)->nullable();
            $table->unsignedBigInteger('total_clicks')->default(0);
            $table->unsignedBigInteger('unique_clicks')->default(0);
            $table->timestamps();

            $table->unique(['affiliate_profile_id', 'metric_date', 'dimension_key'], 'affiliate_click_daily_metrics_profile_date_dimension_unique');
            $table->index(['affiliate_profile_id', 'metric_date'], 'affiliate_click_daily_metrics_profile_date_idx');
            $table->index(['affiliate_profile_id', 'campaign_code', 'metric_date'], 'affiliate_click_daily_metrics_profile_campaign_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_click_daily_metrics');
        Schema::dropIfExists('affiliate_visitor_windows');
        Schema::dropIfExists('affiliate_click_events');
    }
};
