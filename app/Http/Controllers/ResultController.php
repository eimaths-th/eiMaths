<?php

namespace App\Http\Controllers;

use App\Domain\Elearning\{QuizResultAccess, QuizResultReview};
use App\Models\{ContactQuiz, Test};
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function show(Test $test): View|RedirectResponse
    {
        if (app(QuizResultAccess::class)->AuthorizePersonalOrStaff($test)) {
            return redirect()->route('backoffice.quiz-results.show', $test);
        }
        $can_view_videos = Auth::guard('web')->check();
        if (Auth::guard('student')->check()) {
            $can_view_videos = ContactQuiz::query()
                ->where('contact_id', Auth::guard('student')->id())
                ->where('quiz_id', $test->quiz_id)->exists();
        }
        return view('home.result', app(QuizResultReview::class)->Build($test, $can_view_videos));
    }
}
