<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\StudentLoginEvent;
use App\Models\Test;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudentActivityController extends Controller
{
    private const PAGE_SIZE = 20;

    public function __construct()
    {
        $this->middleware('permission:student-activity-list');
    }

    public function Index(Request $request, Contact $contact): View
    {
        $login_events = StudentLoginEvent::query()
            ->where('contact_id', $contact->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(self::PAGE_SIZE, ['*'], 'login_page')
            ->withQueryString();

        return view('contacts.activity', [
            'student' => $contact,
            'login_events' => $login_events,
            'quiz_attempts' => $request->user()->can('quiz-results-list') ? $this->QuizAttempts($contact) : null,
            'can_view_answers' => $request->user()->can('quiz-results-view'),
        ]);
    }

    private function QuizAttempts(Contact $student): LengthAwarePaginator
    {
        // tests.user_id also holds staff IDs, so the participant type is part of the student's identity.
        return Test::query()
            ->where('type_user', 'student')
            ->where('user_id', $student->id)
            ->with('quiz:id,title')
            ->withCount('questions')
            ->latest()
            ->paginate(self::PAGE_SIZE, ['*'], 'quiz_page')
            ->withQueryString();
    }
}
