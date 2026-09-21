<?php

namespace App\Support\Branch;

use App\Models\Contact;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Validation\ValidationException;

final class BranchTransferService
{
    public function Transfer(User $actor, array $input, bool $is_batch): array
    {
        $record_ids = $is_batch ? $input['record_ids'] : [(int) $input['record_id']];

        return DB::transaction(function () use ($actor, $input, $is_batch, $record_ids): array {
            $this->ValidateDestination($actor, $input, $is_batch);

            $record_class = $input['type'] === 'user' ? User::class : Contact::class;
            $query = $record_class::query();
            if ($input['type'] === 'user') {
                $query->with('roles.permissions');
            }
            $records = $is_batch
                ? $query->whereIn('id', $record_ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id')
                : collect([$query->lockForUpdate()->findOrFail($record_ids[0])])->keyBy('id');

            $pending_orders = $this->PendingOrders($input, $records);
            $problems = [];
            foreach ($record_ids as $record_id) {
                $record = $records->get($record_id);
                $problems[] = $record
                    ? $this->InspectRecord($actor, $record, $input, $is_batch, $pending_orders)
                    : $this->Problem($record_id, 'RECORD_UNAVAILABLE', __('centre-management.transfers.bulk.unavailable'), 422);
            }
            $problems = array_values(array_filter($problems));

            if ($problems !== []) {
                $this->Reject($problems, $is_batch);
            }

            return $this->Persist($actor, $input, $record_class, $records, $record_ids);
        });
    }

    private function ValidateDestination(User $actor, array $input, bool $is_batch): void
    {
        try {
            BranchAccess::assertBranch($actor, $input['to_branch_id'], true);
        } catch (HttpExceptionInterface $exception) {
            if (!$is_batch) {
                throw $exception;
            }
            if ($exception->getStatusCode() === 403) {
                abort(403, __('centre-management.transfers.bulk.forbidden'));
            }
            throw ValidationException::withMessages([
                'to_branch_id' => __('centre-management.transfers.bulk.destination_invalid'),
            ]);
        }

        if ($is_batch && $input['type'] === 'user' && isset($input['position_id']) && $input['position_id'] !== null) {
            $is_valid = Position::query()
                ->whereKey($input['position_id'])
                ->where('department_id', $input['to_branch_id'])
                ->where('status', 1)
                ->exists();
            if (!$is_valid) {
                throw ValidationException::withMessages([
                    'position_id' => __('centre-management.transfers.bulk.department_invalid'),
                ]);
            }
        }
    }

    private function PendingOrders(array $input, $records): array
    {
        if ($input['type'] !== 'student' || $records->isEmpty()) {
            return [];
        }

        $pending_orders = [];
        $orders = DB::table('orders')
            ->whereIn('cid', $records->keys())
            ->where(fn ($statuses) => $statuses->whereNull('status')->orWhereIn('status', ['0', '1']))
            ->get(['cid', 'centre']);
        foreach ($orders as $order) {
            $pending_orders[(int) $order->cid.':'.(int) $order->centre] = true;
        }

        return $pending_orders;
    }

    private function InspectRecord(
        User $actor,
        User|Contact $record,
        array $input,
        bool $is_batch,
        array $pending_orders,
    ): ?array {
        $source_branch_id = (int) ($record instanceof User ? $record->department_id : $record->centre);
        try {
            BranchAccess::assertBranch($actor, $source_branch_id);
        } catch (HttpExceptionInterface $exception) {
            return $this->Problem(
                $record->id,
                'SOURCE_UNAVAILABLE',
                $is_batch ? __('centre-management.transfers.bulk.unavailable') : $exception->getMessage(),
                $exception->getStatusCode(),
            );
        }

        if ($is_batch && $source_branch_id !== (int) ($input['expected_from_branch_ids'][$record->id] ?? 0)) {
            return $this->Problem($record->id, 'SOURCE_CHANGED', __('centre-management.transfers.bulk.source_changed'), 409);
        }
        if ($source_branch_id === (int) $input['to_branch_id']) {
            return $this->Problem($record->id, 'SAME_BRANCH', __('centre-management.transfers.errors.different_destination'), 422);
        }

        if ($record instanceof User) {
            try {
                BranchAccess::assertManageUser($actor, $record);
                abort_if($record->hasAnyRole(['SystemAdmin', 'Admin']), 422, 'Central administrators must remain in HQ.');
                foreach ($record->roles as $role) {
                    BranchAccess::assertAssignableRole($actor, $role, (int) $input['to_branch_id']);
                }
            } catch (HttpExceptionInterface $exception) {
                return $this->Problem(
                    $record->id,
                    'ROLE_NOT_ALLOWED',
                    $is_batch ? __('centre-management.transfers.bulk.role_invalid') : $exception->getMessage(),
                    $exception->getStatusCode(),
                );
            }
        } elseif (isset($pending_orders[$record->id.':'.$source_branch_id])) {
            return $this->Problem($record->id, 'PENDING_ORDERS', __('centre-management.transfers.errors.pending_orders'), 422);
        }

        return null;
    }

    private function Persist(User $actor, array $input, string $record_class, $records, array $record_ids): array
    {
        $context = app(BranchContext::class);
        $previous_allow_transfer = $context->allowTransfer;
        $results = [];

        try {
            $context->allowTransfer = true;
            foreach ($records as $record) {
                $branch_column = $record instanceof User ? 'department_id' : 'centre';
                $source_branch_id = (int) $record->getAttribute($branch_column);
                if ($record instanceof User) {
                    $record->position_id = $input['position_id'] ?? null;
                } else {
                    $record->order = 0;
                }
                $record->setAttribute($branch_column, $input['to_branch_id']);
                if (!$record->save()) {
                    throw new \RuntimeException('Branch transfer save was rejected.');
                }
                DB::table('branch_transfers')->insert([
                    'subject_type' => $record_class,
                    'subject_id' => $record->id,
                    'from_branch_id' => $source_branch_id,
                    'to_branch_id' => $input['to_branch_id'],
                    'actor_id' => $actor->id,
                    'reason' => $input['reason'],
                    'created_at' => now(),
                ]);
                if ($record instanceof User) {
                    $record->tokens()->delete();
                }
                $results[$record->id] = [
                    'id' => $record->id,
                    'from_branch_id' => $source_branch_id,
                    'to_branch_id' => (int) $input['to_branch_id'],
                ];
            }
        } finally {
            $context->allowTransfer = $previous_allow_transfer;
        }

        return array_map(fn ($record_id) => $results[$record_id], $record_ids);
    }

    private function Reject(array $problems, bool $is_batch): never
    {
        if (!$is_batch) {
            abort($problems[0]['http_status'], $problems[0]['message']);
        }

        $statuses = array_column($problems, 'http_status');
        $http_status = in_array(403, $statuses, true)
            ? 403
            : (in_array(409, $statuses, true) ? 409 : 422);
        $record_errors = array_map(function (array $problem): array {
            unset($problem['http_status']);
            return $problem;
        }, $problems);
        throw new BranchTransferRejected($record_errors, $http_status);
    }

    private function Problem(int $record_id, string $code, string $message, int $http_status): array
    {
        return compact('record_id', 'code', 'message', 'http_status');
    }
}
