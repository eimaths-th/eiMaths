<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'affiliate_contents';
    private const CAMPAIGN_INDEX = 'affiliate_contents_campaign_code_unique';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table): void {
            if (! Schema::hasColumn(self::TABLE, 'destination_target')) {
                $table->string('destination_target', 2048)->nullable();
            }
            if (! Schema::hasColumn(self::TABLE, 'campaign_code')) {
                $table->string('campaign_code', 96)->nullable();
            }
            if (! Schema::hasColumn(self::TABLE, 'publish_starts_at')) {
                $table->dateTime('publish_starts_at')->nullable();
            }
            if (! Schema::hasColumn(self::TABLE, 'publish_ends_at')) {
                $table->dateTime('publish_ends_at')->nullable();
            }
            if (! Schema::hasColumn(self::TABLE, 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
            }
            if (! Schema::hasColumn(self::TABLE, 'archived_by')) {
                $table->unsignedBigInteger('archived_by')->nullable();
            }
            if (! Schema::hasColumn(self::TABLE, 'archived_at')) {
                $table->dateTime('archived_at')->nullable();
            }
            if (! Schema::hasColumn(self::TABLE, 'deleted_at')) {
                $table->softDeletes();
            }
        });

        DB::table(self::TABLE)
            ->where(function ($query): void {
                $query->whereNull('destination_target')->orWhereNull('campaign_code');
            })
            ->orderBy('id')
            ->chunkById(100, function ($contents): void {
                foreach ($contents as $content) {
                    $attributes = [];
                    if ($content->destination_target === null) {
                        $attributes['destination_target'] = '/';
                    }
                    if ($content->campaign_code === null) {
                        $attributes['campaign_code'] = 'legacy-' . $content->id;
                    }
                    if ($attributes !== []) {
                        DB::table(self::TABLE)->where('id', $content->id)->update($attributes);
                    }
                }
            });

        if (! Schema::hasIndex(self::TABLE, self::CAMPAIGN_INDEX, 'unique')) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->unique('campaign_code', self::CAMPAIGN_INDEX);
            });
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->foreign('updated_by', 'affiliate_contents_updated_by_fk')
                    ->references('id')->on('users')->nullOnDelete();
                $table->foreign('archived_by', 'affiliate_contents_archived_by_fk')
                    ->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                if (Schema::hasColumn(self::TABLE, 'updated_by')) {
                    $table->dropForeign('affiliate_contents_updated_by_fk');
                }
                if (Schema::hasColumn(self::TABLE, 'archived_by')) {
                    $table->dropForeign('affiliate_contents_archived_by_fk');
                }
            });
        }

        Schema::table(self::TABLE, function (Blueprint $table): void {
            if (Schema::hasIndex(self::TABLE, self::CAMPAIGN_INDEX, 'unique')) {
                $table->dropUnique(self::CAMPAIGN_INDEX);
            }
            $columns = array_values(array_filter([
                Schema::hasColumn(self::TABLE, 'destination_target') ? 'destination_target' : null,
                Schema::hasColumn(self::TABLE, 'campaign_code') ? 'campaign_code' : null,
                Schema::hasColumn(self::TABLE, 'publish_starts_at') ? 'publish_starts_at' : null,
                Schema::hasColumn(self::TABLE, 'publish_ends_at') ? 'publish_ends_at' : null,
                Schema::hasColumn(self::TABLE, 'updated_by') ? 'updated_by' : null,
                Schema::hasColumn(self::TABLE, 'archived_by') ? 'archived_by' : null,
                Schema::hasColumn(self::TABLE, 'archived_at') ? 'archived_at' : null,
                Schema::hasColumn(self::TABLE, 'deleted_at') ? 'deleted_at' : null,
            ]));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
