<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_detail_lists', function (Blueprint $table): void {
            $table->unsignedBigInteger('active_book_shipment_item_id')
                ->nullable()
                ->after('received_by');
            $table->unique(
                'active_book_shipment_item_id',
                'order_detail_shipment_item_unique'
            );
            $table->foreign(
                'active_book_shipment_item_id',
                'order_detail_shipment_item_fk'
            )
                ->references('id')
                ->on('book_shipment_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('order_detail_lists', function (Blueprint $table): void {
                $table->dropColumn('active_book_shipment_item_id');
            });

            return;
        }

        Schema::table('order_detail_lists', function (Blueprint $table): void {
            $table->dropForeign('order_detail_shipment_item_fk');
            $table->dropUnique('order_detail_shipment_item_unique');
            $table->dropColumn('active_book_shipment_item_id');
        });
    }
};
