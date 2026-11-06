<?php

namespace Tests\Feature\Elearning;

use App\Models\{Quiz, Test as QuizTest, User};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuizPortalThemeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    #[Test]
    public function PreservesHomeSelectionLinksAcrossAllSteps(): void
    {
        foreach (['en', 'th'] as $locale) {
            app()->setLocale($locale);
            foreach ([0, 1, 2, 3] as $step) {
                $quiz = $this->CreateAttempt()->quiz;
                $quizzes = collect([$quiz]);
                $document = $this->RenderPage('home.home', 'home3', [
                    'levelCheck' => $step, 'level' => 'P1', 'term' => '1', 'section' => '2',
                    'public_quizzes_level' => $quizzes->groupBy('level'),
                    'public_quizzes_term' => match ($step) { 1 => $quizzes->groupBy('term'), 2 => $quizzes->groupBy('section'), default => $quizzes },
                ]);
                $expected_url = match ($step) {
                    0 => route('home.level', 'P1'), 1 => route('home.term', ['P1', '1']),
                    2 => route('home.section', ['P1', '1', '2']), 3 => route('quiz.show', 'addition'),
                };
                $this->assertSame(1, $document->query('//main//a[@href="' . $expected_url . '"]')->length);
                $this->AssertPortalHeading($document);
                $this->assertStringNotContainsString('quiz-management.home.', $document->evaluate('string(//main)'));
            }
        }
    }

    #[Test]
    public function RendersHomeEmptyStates(): void
    {
        foreach ([0, 1, 2, 3] as $step) {
            $document = $this->RenderPage('home.home', 'home3', [
                'levelCheck' => $step, 'public_quizzes_level' => collect(), 'public_quizzes_term' => collect(),
            ]);
            $this->assertStringContainsString(__('quiz-management.home.empty_title'), $document->evaluate('string(//main)'));
        }
    }

    #[Test]
    public function PreservesLeaderboardFilterAndRanks(): void
    {
        foreach ([1, 2] as $page) {
            $attempts = $page === 1 ? [$this->CreateAttempt(), $this->CreateAttempt(43), $this->CreateAttempt(44), $this->CreateAttempt(45)] : [$this->CreateAttempt()];
            $document = $this->RenderLeaderboard($attempts, 11, $page);
            $this->assertSame($page === 1 ? 3 : 0, $document->query('//main//div[contains(@class,"podium-card")]')->length);
            $this->assertSame($page === 1 ? '4' : '11', trim($document->evaluate('string(//main//div[@class="rank-badge"])')));
            $this->assertSame(route('submit.leaderboard'), $document->evaluate('string(//form[@id="quiz-form"]/@action)'));
            $this->assertSame('POST', $document->evaluate('string(//form[@id="quiz-form"]/@method)'));
            $this->assertSame(1, $document->query('//form//input[@name="_token"]')->length);
            $this->assertSame('leaderboard', $document->evaluate('string(//input[@name="route"]/@value)'));
            $this->assertSame('addition', $document->evaluate('string(//select/option[@selected]/@value)'));
            $this->assertStringContainsString('page=', $document->evaluate('string(//main//nav//a/@href)'));
            $this->assertSame(1, $document->query('//label[@for="leaderboard-quiz"]')->length);
            $this->assertSame(1, $document->query('//select[@id="leaderboard-quiz"]')->length);
            foreach (['Test Learner', 'Addition practice', '0/2', '00:00:00'] as $expected) {
                $this->assertStringContainsString($expected, $document->evaluate('string(//main)'));
            }
            $this->AssertPortalHeading($document);
        }
    }

    #[Test]
    public function RendersLeaderboardEmptyAndZeroScores(): void
    {
        $this->assertStringContainsString('No Results Yet', $this->RenderLeaderboard([], 0)->evaluate('string(//main)'));
        $attempt = $this->CreateAttempt();
        $attempt->quiz->questions_count = 0;
        $document = $this->RenderLeaderboard([$attempt]);
        $this->assertStringContainsString('0/0', $document->evaluate('string(//main)'));
        $this->assertStringContainsString('0%', $document->evaluate('string(//main)'));
    }

    #[Test]
    public function PreservesMyResultsSummaryAndAnswerLinks(): void
    {
        $document = $this->RenderResults([$this->CreateAttempt(), $this->CreateAttempt(43, 2)], 11);
        $summary = $document->query('//div[@class="summary-stat-number"]');
        foreach (['2', '2/4', '50%', '1'] as $index => $expected) {
            $this->assertSame($expected, trim($summary->item($index)->textContent));
        }
        foreach (['0/2', '2/2', '00:00:00', '00:01:02', '02/11/2026', 'P1'] as $expected) {
            $this->assertStringContainsString($expected, $document->evaluate('string(//main)'));
        }
        foreach ([42, 43] as $attempt_id) {
            $link = $document->query('//main//a[@href="' . route('results.show', $attempt_id) . '"]')->item(0);
            $this->assertNotNull($link);
            $this->assertStringContainsString('#' . $attempt_id, $link->getAttribute('aria-label'));
        }
        $this->assertGreaterThan(0, $document->query('//main//a[contains(@href,"page=2")]')->length);
        $this->AssertPortalHeading($document);
    }

    #[Test]
    public function RendersMyResultsEmptyAndZeroScores(): void
    {
        $this->assertStringContainsString('No test results found', $this->RenderResults([], 0)->evaluate('string(//main)'));
        $attempt = $this->CreateAttempt();
        $attempt->quiz->questions_count = 0;
        $document = $this->RenderResults([$attempt]);
        $this->assertStringContainsString('0/0', $document->evaluate('string(//main)'));
        $this->assertStringContainsString('0%', $document->evaluate('string(//main)'));
    }

    #[Test]
    public function EscapesLongPortalText(): void
    {
        $attempt = $this->CreateAttempt();
        $long_title = str_repeat('Long title แบบทดสอบ ', 20) . '<script>alert(1)</script>';
        $attempt->quiz->title = $long_title;
        $attempt->user->name = $long_title;
        foreach ([$this->RenderResults([$attempt]), $this->RenderLeaderboard([$attempt])] as $document) {
            $this->assertStringContainsString($long_title, $document->evaluate('string(//main)'));
            $this->assertSame(0, $document->query('//main//script')->length);
        }
    }

    private function RenderResults(array $attempts, int $total = 1): \DOMXPath
    {
        return $this->RenderPage('home.result-list', 'myresults', [
            'tests' => new LengthAwarePaginator($attempts, $total, 10, 1, ['path' => route('myresults')]),
        ]);
    }

    private function RenderLeaderboard(array $attempts, int $total = 1, int $page = 1): \DOMXPath
    {
        return $this->RenderPage('home.leaderboard', 'leaderboard', [
            'tests' => new LengthAwarePaginator($attempts, $total, 10, $page, ['path' => route('leaderboard', 'addition')]),
            'quizzes' => collect([$this->CreateAttempt()->quiz]), 'quiz_slug' => 'addition',
        ]);
    }

    private function AssertPortalHeading(\DOMXPath $document): void
    {
        $this->assertSame(1, $document->query('//main[@class="quiz-portal-main"]//h1')->length);
    }

    private function CreateAttempt(int $attempt_id = 42, int $score = 0): QuizTest
    {
        $quiz = (new Quiz)->forceFill(['id' => 5, 'slug' => 'addition', 'title' => 'Addition practice', 'level' => 'P1', 'term' => '1', 'section' => '2', 'questions_count' => 2]);
        $participant = (new User)->forceFill(['id' => 7, 'name' => 'Test Learner']);
        return (new QuizTest)->forceFill(['id' => $attempt_id, 'type_user' => 'user', 'result' => $score, 'time_spent' => $score ? 62 : 0, 'created_at' => '2026-11-02 10:00:00'])
            ->setRelation('quiz', $quiz)->setRelation('user', $participant)->setRelation('student', null);
    }

    private function RenderPage(string $view_name, string $route_name, array $view_data): \DOMXPath
    {
        request()->setRouteResolver(fn () => Route::getRoutes()->getByName($route_name));
        $html = (string) $this->view($view_name, $view_data);
        $document = new \DOMDocument;
        $previous_error_mode = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">' . $html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous_error_mode);
        }
        return new \DOMXPath($document);
    }
}
