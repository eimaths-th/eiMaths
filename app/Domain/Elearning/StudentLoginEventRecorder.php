<?php

namespace App\Domain\Elearning;

use App\Models\Contact;
use App\Models\StudentLoginEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentLoginEventRecorder
{
    public function RecordLogin(Contact $student): void
    {
        $this->Record($student, StudentLoginEvent::LOGIN);
    }

    public function RecordLogout(Contact $student): void
    {
        $this->Record($student, StudentLoginEvent::LOGOUT);
    }

    private function Record(Contact $student, string $event_type): void
    {
        try {
            // A staff session on the same browser must not trigger branch write checks for a student's own login.
            DB::table('student_login_events')->insert([
                'contact_id' => $student->getKey(),
                'event_type' => $event_type,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable) {
            Log::warning('STUDENT_LOGIN_EVENT_FAILED', [
                'contact_id' => $student->getKey(),
                'event_type' => $event_type,
            ]);
        }
    }
}
