<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('contacts')
            ->where('type', 'student')
            ->whereNull('new_student_order_id')
            ->select('id')
            ->chunkById(250, function ($students): void {
                foreach ($students as $student) {
                    DB::transaction(function () use ($student): void {
                        // Transfers lock this same row before changing its branch or
                        // inserting their history, so recheck everything after locking.
                        $current = DB::table('contacts')
                            ->where('id', $student->id)
                            ->lockForUpdate()
                            ->first(['id', 'type', 'centre', 'order', 'new_student_order_id']);

                        if ($current === null
                            || $current->type !== 'student'
                            || $current->new_student_order_id !== null
                            || (int) $current->centre < 1
                            || (int) $current->order < 1) {
                            return;
                        }

                        // Keep the historical discriminator literal: migrations must
                        // not change behavior when application model classes change.
                        $transfer = DB::table('branch_transfers')
                            ->where('subject_type', 'App\\Models\\Contact')
                            ->where('subject_id', $current->id)
                            ->orderByDesc('id')
                            ->first(['id', 'to_branch_id', 'created_at']);

                        if ($transfer === null
                            || $transfer->created_at === null
                            || (int) $transfer->to_branch_id !== (int) $current->centre) {
                            return;
                        }

                        // A stale current-order pointer is not evidence of a new
                        // enrolment in the destination branch.
                        $currentOrder = DB::table('orders')
                            ->where('id', $current->order)
                            ->where('cid', $current->id)
                            ->where('centre', $current->centre)
                            ->where('created_at', '>=', $transfer->created_at)
                            ->lockForUpdate()
                            ->first(['id']);

                        if ($currentOrder === null) {
                            return;
                        }

                        // Resolve the first order of this visit, independently of
                        // whether its books have already been reserved or received.
                        $initialOrder = DB::table('orders')
                            ->where('cid', $current->id)
                            ->where('centre', $current->centre)
                            ->where('created_at', '>=', $transfer->created_at)
                            ->orderBy('created_at')
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->first(['id', 'created_at']);

                        if ($initialOrder === null) {
                            return;
                        }

                        // Second-resolution timestamps cannot distinguish an order
                        // from an earlier visit during a same-second return transfer.
                        // Include departures to cover an original A -> B -> A visit.
                        $ambiguousReturn = $initialOrder->created_at === $transfer->created_at
                            && DB::table('branch_transfers')
                                ->where('subject_type', 'App\\Models\\Contact')
                                ->where('subject_id', $current->id)
                                ->where('id', '<', $transfer->id)
                                ->where('created_at', $transfer->created_at)
                                ->where(function ($query) use ($current): void {
                                    $query->where('from_branch_id', $current->centre)
                                        ->orWhere('to_branch_id', $current->centre);
                                })
                                ->exists();

                        if ($ambiguousReturn) {
                            return;
                        }

                        DB::table('contacts')
                            ->where('id', $current->id)
                            ->whereNull('new_student_order_id')
                            ->update(['new_student_order_id' => $initialOrder->id]);
                    }, 3);
                }
            });
    }

    public function down(): void
    {
        // Data repair only. Do not erase valid enrolment links during rollback.
    }
};
