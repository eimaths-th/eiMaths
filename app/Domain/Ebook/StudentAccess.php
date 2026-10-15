<?php

namespace App\Domain\Ebook;

use App\Models\Contact;
use App\Services\FastHistoryService;

final class StudentAccess
{
    public function __construct(private FastHistoryService $courses) {}

    /** @return array{code: string, message: string}|null */
    public function denial(Contact $student): ?array
    {
        if ((int) $student->discontinued !== 0) {
            return ['code' => 'account_discontinued', 'message' => 'บัญชีนี้ไม่สามารถใช้งานได้'];
        }

        // Share the staff renewal rules, including paid/free lessons and course dates.
        // Re-evaluate on each request: a cached Session must not extend course access.
        $status = $this->courses->BuildRenewalStatus($student);
        if ($status['status'] === 'active') {
            return null;
        }

        return match ($status['reason']) {
            'course_expired' => [
                'code' => 'course_expired',
                'message' => 'คอร์สเรียนหมดอายุแล้ว กรุณาติดต่อศูนย์เพื่อดำเนินการต่อคอร์ส',
            ],
            'course_exhausted' => [
                'code' => 'course_exhausted',
                'message' => 'ใช้จำนวนครั้งเรียนครบแล้ว กรุณาติดต่อศูนย์เพื่อดำเนินการต่อคอร์ส',
            ],
            default => [
                'code' => 'course_data_unavailable',
                'message' => 'ไม่สามารถยืนยันสิทธิ์คอร์สเรียนได้ กรุณาติดต่อศูนย์เพื่อตรวจสอบข้อมูล',
            ],
        };
    }
}
