<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->string('line_user_id')->nullable()->after('line_id');
            $table->string('line_display_name')->nullable()->after('line_user_id');
            $table->text('line_picture_url')->nullable()->after('line_display_name');
            $table->string('line_follow_status')->nullable()->after('line_picture_url');
            $table->timestamp('line_followed_at')->nullable()->after('line_follow_status');
            $table->timestamp('line_last_message_at')->nullable()->after('line_followed_at');
            $table->string('line_conversation_state')->nullable()->after('line_last_message_at');
            $table->string('line_ref')->nullable()->after('line_conversation_state');
            $table->string('line_source')->nullable()->after('line_ref');

            $table->index('line_user_id');
            $table->index('created_from');
            $table->index('line_follow_status');
        });
    }

    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropIndex(['line_user_id']);
            $table->dropIndex(['created_from']);
            $table->dropIndex(['line_follow_status']);
            $table->dropColumn([
                'line_user_id',
                'line_display_name',
                'line_picture_url',
                'line_follow_status',
                'line_followed_at',
                'line_last_message_at',
                'line_conversation_state',
                'line_ref',
                'line_source',
            ]);
        });
    }
};
