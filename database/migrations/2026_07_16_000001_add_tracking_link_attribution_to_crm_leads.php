<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->foreignId('tracking_link_id')
                ->nullable()
                ->after('utm_term')
                ->constrained('tracking_links')
                ->nullOnDelete();
            $table->string('tracking_link_code', 40)->nullable()->after('tracking_link_id');
            $table->string('tracking_link_name')->nullable()->after('tracking_link_code');
            $table->foreignId('tracking_link_creator_id')
                ->nullable()
                ->after('tracking_link_name')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('tracking_link_creator_name')->nullable()->after('tracking_link_creator_id');

            $table->index('tracking_link_code');
            $table->index('tracking_link_creator_name');
        });

        $groupedLinks = DB::table('tracking_links')
            ->get()
            ->groupBy(fn ($link) => implode('|', [
                $link->utm_source,
                $link->utm_medium,
                $link->utm_campaign,
                $link->utm_content ?? '',
                $link->utm_term ?? '',
            ]));

        foreach ($groupedLinks as $matchingLinks) {
            if ($matchingLinks->count() !== 1) {
                continue;
            }

            $link = $matchingLinks->first();
            $leadQuery = DB::table('crm_leads')
                ->whereNull('tracking_link_id')
                ->where('utm_source', $link->utm_source)
                ->where('utm_medium', $link->utm_medium)
                ->where('utm_campaign', $link->utm_campaign);

            foreach (['utm_content', 'utm_term'] as $field) {
                if ($link->{$field} === null || $link->{$field} === '') {
                    $leadQuery->where(function ($query) use ($field) {
                        $query->whereNull($field)->orWhere($field, '');
                    });
                } else {
                    $leadQuery->where($field, $link->{$field});
                }
            }

            $leadQuery->update([
                'tracking_link_id' => $link->id,
                'tracking_link_code' => $link->tracking_code,
                'tracking_link_name' => $link->name,
                'tracking_link_creator_id' => $link->created_by,
                'tracking_link_creator_name' => $link->created_by_name ?: 'System',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropForeign(['tracking_link_id']);
            $table->dropForeign(['tracking_link_creator_id']);
            $table->dropIndex(['tracking_link_code']);
            $table->dropIndex(['tracking_link_creator_name']);
            $table->dropColumn([
                'tracking_link_id',
                'tracking_link_code',
                'tracking_link_name',
                'tracking_link_creator_id',
                'tracking_link_creator_name',
            ]);
        });
    }
};
