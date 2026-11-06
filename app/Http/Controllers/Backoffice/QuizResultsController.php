<?php

namespace App\Http\Controllers\Backoffice;

use App\Domain\Elearning\{QuizResultAccess, QuizResultReview};
use App\Http\Controllers\Controller;
use App\Models\{Quiz, Test};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{RedirectResponse, Request};

class QuizResultsController extends Controller
{
    private const PAGE_SIZES = [10, 20, 50, 100];
    private const DEFAULT_PAGE_SIZE = 20;

    public function __construct(private readonly QuizResultAccess $result_access, private readonly QuizResultReview $result_review) {}

    public function Index(Request $request, string $quiz_slug = '0'): View
    {
        $actor = $this->result_access->AuthorizeStaff('quiz-results-list');
        $quiz_id = $quiz_slug !== '0' ? Quiz::where('slug', $quiz_slug)->value('id') : null;
        $per_page = $this->PageSize($request);
        $tests = Test::query()
            ->where(function ($participants): void {
                $participants->where(function ($students): void {
                    $students->where('type_user', 'student')->whereHas('student');
                })->orWhere(function ($users): void {
                    $users->where('type_user', 'user')->whereHas('user');
                });
            })
            ->with(['student', 'user:id,name', 'quiz:id,title,level,term,section'])
            ->when($quiz_id, fn ($attempts) => $attempts->where('quiz_id', $quiz_id))
            ->withCount('questions')->latest()->paginate($per_page)->withQueryString();
        return view('quiz-results.index', [
            'tests' => $tests, 'quizzes' => Quiz::all(), 'quiz_slug' => $quiz_slug,
            'header' => __('quiz-management.preview.title'), 'perPage' => $per_page,
            'perPageOptions' => self::PAGE_SIZES, 'can_view_answers' => $actor->can('quiz-results-view'),
            'answer_route_name' => 'backoffice.quiz-results.show',
        ]);
    }

    public function Show(Test $test): View
    {
        $this->result_access->AuthorizeStaff('quiz-results-view');
        return view('quiz-results.show', $this->result_review->Build($test, true));
    }

    public function RedirectLegacy(Request $request, string $quiz_slug = '0'): RedirectResponse
    {
        $this->result_access->AuthorizeStaff('quiz-results-list');
        $parameters = $quiz_slug === '0' ? [] : ['quiz_slug' => $quiz_slug];
        if ($request->has('per_page')) {
            $parameters['per_page'] = $this->PageSize($request);
        }
        $page = filter_var($request->query('page'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($page !== false && $page !== null) {
            $parameters['page'] = $page;
        }
        return redirect()->route('backoffice.quiz-results.index', $parameters);
    }

    public function Filter(Request $request): RedirectResponse
    {
        $this->result_access->AuthorizeStaff('quiz-results-list');
        $validated = $request->validate(['quiz_slug' => 'required|string']);
        return redirect()->route('backoffice.quiz-results.index', $validated['quiz_slug'] === '0' ? [] : ['quiz_slug' => $validated['quiz_slug']]);
    }

    private function PageSize(Request $request): int
    {
        $page_size = filter_var($request->input('per_page'), FILTER_VALIDATE_INT);
        return in_array($page_size, self::PAGE_SIZES, true) ? $page_size : self::DEFAULT_PAGE_SIZE;
    }
}
