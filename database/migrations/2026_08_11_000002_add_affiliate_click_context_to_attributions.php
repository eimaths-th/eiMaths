<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SNAPSHOT_COLUMNS = [
        'affiliate_click_event_id',
        'affiliate_content_id',
        'affiliate_campaign_code',
        'affiliate_source_type',
        'affiliate_unique_window_key',
        'affiliate_captured_destination',
    ];

    public function up(): void
    {
        $this->AddContextColumns('affiliate_attributions');
        $this->AddContextColumns('courses_pennding');
    }

    public function down(): void
    {
        $this->DropContextColumns('affiliate_attributions');
        $this->DropContextColumns('courses_pennding');
    }

    private function AddContextColumns(string $table_name): void
    {
        if (! Schema::hasTable($table_name)) {
            return;
        }

        Schema::table($table_name, function (Blueprint $table) use ($table_name): void {
            if (! Schema::hasColumn($table_name, 'affiliate_click_event_id')) {
                $table->unsignedBigInteger('affiliate_click_event_id')->nullable();
            }
            if (! Schema::hasColumn($table_name, 'affiliate_content_id')) {
                $table->unsignedBigInteger('affiliate_content_id')->nullable();
            }
            if (! Schema::hasColumn($table_name, 'affiliate_campaign_code')) {
                $table->string('affiliate_campaign_code', 96)->nullable();
            }
            if (! Schema::hasColumn($table_name, 'affiliate_source_type')) {
                $table->string('affiliate_source_type', 16)->nullable();
            }
            if (! Schema::hasColumn($table_name, 'affiliate_unique_window_key')) {
                $table->char('affiliate_unique_window_key', 26)->nullable();
            }
            if (! Schema::hasColumn($table_name, 'affiliate_captured_destination')) {
                $table->string('affiliate_captured_destination', 2048)->nullable();
            }
            $table->index('affiliate_campaign_code', $table_name . '_campaign_snapshot_idx');
            $table->index('affiliate_unique_window_key', $table_name . '_window_snapshot_idx');
        });

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement("CREATE TRIGGER IF NOT EXISTS {$table_name}_click_event_set_null AFTER DELETE ON affiliate_click_events BEGIN UPDATE {$table_name} SET affiliate_click_event_id = NULL WHERE affiliate_click_event_id = OLD.id; END");
            DB::statement("CREATE TRIGGER IF NOT EXISTS {$table_name}_content_set_null AFTER DELETE ON affiliate_contents BEGIN UPDATE {$table_name} SET affiliate_content_id = NULL WHERE affiliate_content_id = OLD.id; END");
            return;
        }

        Schema::table($table_name, function (Blueprint $table) use ($table_name): void {
            $table->foreign('affiliate_click_event_id', $table_name . '_click_event_fk')
                ->references('id')
                ->on('affiliate_click_events')
                ->nullOnDelete();
            $table->foreign('affiliate_content_id', $table_name . '_content_fk')
                ->references('id')
                ->on('affiliate_contents')
                ->nullOnDelete();
        });
    }

    private function DropContextColumns(string $table_name): void
    {
        if (! Schema::hasTable($table_name)) {
            return;
        }

        Schema::table($table_name, function (Blueprint $table) use ($table_name): void {
            if (Schema::getConnection()->getDriverName() !== 'sqlite' && Schema::hasColumn($table_name, 'affiliate_click_event_id')) {
                $table->dropForeign($table_name . '_click_event_fk');
            }
            if (Schema::getConnection()->getDriverName() !== 'sqlite' && Schema::hasColumn($table_name, 'affiliate_content_id')) {
                $table->dropForeign($table_name . '_content_fk');
            }
            if (Schema::hasColumn($table_name, 'affiliate_campaign_code')) {
                $table->dropIndex($table_name . '_campaign_snapshot_idx');
            }
            if (Schema::hasColumn($table_name, 'affiliate_unique_window_key')) {
                $table->dropIndex($table_name . '_window_snapshot_idx');
            }
            $columns = array_values(array_filter(self::SNAPSHOT_COLUMNS, static fn (string $column): bool => Schema::hasColumn($table_name, $column)));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement("DROP TRIGGER IF EXISTS {$table_name}_click_event_set_null");
            DB::statement("DROP TRIGGER IF EXISTS {$table_name}_content_set_null");
        }
    }
};
