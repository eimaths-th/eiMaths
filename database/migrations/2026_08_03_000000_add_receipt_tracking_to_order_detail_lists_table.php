<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_detail_lists', function (Blueprint $table): void {
            $table->timestamp('received_at')->nullable()->after('bprice');
            $table->foreignId('received_by')
                ->nullable()
                ->after('received_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_detail_lists', function (Blueprint $table): void {
            $table->dropForeign(['received_by']);
            $table->dropColumn(['received_at', 'received_by']);
        });
    }
};
