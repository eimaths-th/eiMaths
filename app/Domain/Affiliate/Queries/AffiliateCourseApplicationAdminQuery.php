<?php

namespace App\Domain\Affiliate\Queries;

use App\Models\CoursePending;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AffiliateCourseApplicationAdminQuery
{
    public function PendingSummary(User $actor): array
    {
        $this->AssertCanReadPendingSummary($actor);

        $pending_query = CoursePending::query()->where('status', '1');
        $pending_count = (clone $pending_query)->count();
        $latest_pending_application = (clone $pending_query)
            ->with('department:id,name')
            ->orderByDesc('id')
            ->first();
        $latest_created_application = CoursePending::query()
            ->select(['id', 'status'])
            ->orderByDesc('id')
            ->first();

        return [
            'pending_count' => $pending_count,
            'latest_event_cursor' => $latest_created_application === null
                ? null
                : 'course-application|'.$latest_created_application->id,
            'latest_event_status' => $latest_created_application?->status === null
                ? null
                : (string) $latest_created_application->status,
            'latest_pending_application' => $this->MapPendingApplication($latest_pending_application),
        ];
    }

    private function AssertCanReadPendingSummary(User $actor): void
    {
        if ((int) $actor->disable !== 0 || ! $actor->can('affiliate-course-application-list')) {
            throw new AuthorizationException;
        }
    }

    private function MapPendingApplication(?CoursePending $application): ?array
    {
        if ($application === null) {
            return null;
        }

        return [
            'id' => (int) $application->id,
            'applicant_name' => trim((string) $application->name) ?: 'ผู้สมัครคอร์ส',
            'course_name' => trim((string) $application->course_name) ?: 'ไม่ระบุคอร์ส',
            'branch_name' => trim((string) $application->department?->name) ?: 'ไม่ระบุสาขา',
            'submitted_at' => $application->created_at?->toIso8601String(),
        ];
    }
}
