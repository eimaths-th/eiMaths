<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->foreignId('new_student_order_id')
                ->nullable()
                ->after('order')
                ->constrained('orders')
                ->nullOnDelete();
        });

        DB::table('contacts')
            ->where('type', 'student')
            ->orderBy('id')
            ->chunkById(250, function ($students): void {
                $student_ids = $students->pluck('id')->all();
                $initial_orders = DB::table('orders')
                    ->select('cid', DB::raw('MIN(id) as initial_order_id'))
                    ->whereIn('cid', $student_ids)
                    ->groupBy('cid')
                    ->pluck('initial_order_id', 'cid');

                foreach ($initial_orders as $student_id => $initial_order_id) {
                    DB::table('contacts')
                        ->where('id', $student_id)
                        ->update(['new_student_order_id' => $initial_order_id]);
                }
            });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('contacts', function (Blueprint $table): void {
                $table->dropColumn('new_student_order_id');
            });

            return;
        }

        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropForeign(['new_student_order_id']);
            $table->dropColumn('new_student_order_id');
        });
    }
};
