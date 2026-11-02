<?php

namespace Tests\Feature\Elearning;

use App\Models\{Quiz, Test as QuizTest, User};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\{DataProvider, Test};
use Tests\TestCase;

class TestPreviewPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        request()->setRouteResolver(fn () => Route::getRoutes()->getByName('tests'));
    }

    #[Test]
    public function PreservesAttemptDataAndResultLinks(): void
    {
        $html = $this->RenderPreview([$this->CreateAttempt()]);
        foreach (['Test Learner', 'Addition practice', 'P1', '0/2', '00:00:00', '02/11/2026', route('results.show', 42)] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
    }

    #[Test]
    public function PreservesQuizSelectionAndPagination(): void
    {
        $document = $this->ReadDocument($this->RenderPreview([$this->CreateAttempt()], 25, 2));
        $this->assertSame(route('submit.leaderboard'), $document->evaluate('string(//form[@id="quiz-form"]/@action)'));
        $this->assertSame('POST', strtoupper($document->evaluate('string(//form[@id="quiz-form"]/@method)')));
        $this->assertSame(1, $document->query('//form[@id="quiz-form"]//input[@name="_token"]')->length);
        $this->assertSame('tests', $document->evaluate('string(//input[@name="route"]/@value)'));
        $this->assertSame('addition', $document->evaluate('string(//select[@name="quiz_slug"]/option[@selected]/@value)'));
        $this->assertSame('10', $document->evaluate('string(//select[@name="per_page"]/option[@selected]/@value)'));
        $sizes = [];
        foreach ($document->query('//select[@name="per_page"]/option') as $option) {
            $sizes[] = $option->getAttribute('value');
        }
        $this->assertSame(['10', '20', '50', '100'], $sizes);
        $this->assertGreaterThan(0, $document->query('//a[contains(@href, "/tests/addition?") and contains(@href, "per_page=10") and contains(@href, "page=3")]')->length);
    }

    #[Test]
    public function RendersTheEmptyState(): void
    {
        $this->assertStringContainsString(__('quiz-management.preview.no_tests'), $this->RenderPreview([], 0));
    }

    public static function Locales(): array
    {
        return [
            ['en', 'Test results', ['Participant', 'Quiz', 'Score', 'Time', 'Date', 'View answers']],
            ['th', 'ผลการทำแบบทดสอบ', ['ผู้ทำ', 'แบบทดสอบ', 'คะแนน', 'เวลา', 'วันที่', 'ดูคำตอบ']],
        ];
    }

    #[Test, DataProvider('Locales')]
    public function RendersAccessibleResultCardsInBothLocales(string $locale, string $heading, array $headers): void
    {
        app()->setLocale($locale);
        $document = $this->ReadDocument($this->RenderPreview([$this->CreateAttempt()]));
        $this->assertSame(1, $document->query('//main//h1')->length);
        $this->assertSame($heading, trim($document->evaluate('string(//main//h1)')));
        $this->assertSame('Addition practice', trim($document->evaluate('string(//main//ul/li/article//h2)')));
        foreach ($headers as $label) {
            $this->assertStringContainsString($label, $document->evaluate('string(//main//article)'));
        }
        foreach ($document->query('//main//select') as $select) {
            $this->assertNotSame('', $select->getAttribute('id'));
            $this->assertSame(1, $document->query('//label[@for="' . $select->getAttribute('id') . '"]')->length);
        }
        $this->assertSame(1, $document->query('//main//ul/li/article')->length);
        $this->assertStringContainsString($headers[5], $document->evaluate('string(//main//article//a)'));
        $this->assertStringNotContainsString('quiz-management.preview.', $document->evaluate('string(//main)'));
    }

    #[Test]
    public function RendersMissingAndLongValues(): void
    {
        $attempt = $this->CreateAttempt();
        $attempt->questions_count = 0;
        $attempt->created_at = null;
        $attempt->setRelation('user', null)->setRelation('quiz', null);
        $document = $this->ReadDocument($this->RenderPreview([$attempt]));
        $this->assertSame(1, $document->query('//main//article')->length);
        $this->assertStringContainsString('0/0', $document->evaluate('string(//main//article)'));
        $this->assertStringContainsString('00:00:00', $document->evaluate('string(//main//article)'));
        $this->assertStringContainsString(__('quiz-management.preview.not_available'), $document->evaluate('string(//main//article)'));
        $attempt = $this->CreateAttempt();
        $long_name = str_repeat('Long learner name ', 15) . '<script>alert(1)</script>';
        $attempt->user->name = $long_name;
        $attempt->quiz->title = str_repeat('Long quiz title ', 15);
        $document = $this->ReadDocument($this->RenderPreview([$attempt]));
        $this->assertStringContainsString($long_name, $document->evaluate('string(//main//article)'));
        $this->assertSame(0, $document->query('//main//article//script')->length);
    }

    #[Test]
    public function KeepsEachAnswerLinkWithItsAttempt(): void
    {
        $first_attempt = $this->CreateAttempt();
        $second_attempt = $this->CreateAttempt();
        $second_attempt->id = 43;
        $second_attempt->user->name = 'Second Learner';
        $document = $this->ReadDocument($this->RenderPreview([$first_attempt, $second_attempt], 2));
        $articles = $document->query('//main//ul/li/article');
        $this->assertSame(2, $articles->length);
        foreach ([42, 43] as $index => $attempt_id) {
            $link = $document->query('.//a', $articles->item($index))->item(0);
            $this->assertSame(route('results.show', $attempt_id), $link->getAttribute('href'));
            $this->assertStringContainsString('#' . $attempt_id, $link->getAttribute('aria-label'));
        }
    }
    private function CreateAttempt(): QuizTest
    {
        $quiz = (new Quiz)->forceFill(['id' => 5, 'slug' => 'addition', 'title' => 'Addition practice', 'level' => 'P1', 'term' => '1', 'section' => '2']);
        $participant = (new User)->forceFill(['id' => 7, 'name' => 'Test Learner']);
        return (new QuizTest)->forceFill(['id' => 42, 'type_user' => 'user', 'result' => 0, 'questions_count' => 2, 'time_spent' => 0, 'created_at' => '2026-11-02 10:00:00'])
            ->setRelation('quiz', $quiz)->setRelation('user', $participant)->setRelation('student', null);
    }

    private function RenderPreview(array $attempts, int $total = 1, int $page = 1): string
    {
        $paginator = new LengthAwarePaginator($attempts, $total, 10, $page, [
            'path' => route('tests', ['quiz_slug' => 'addition']),
            'query' => ['per_page' => 10],
        ]);
        return (string) $this->view('home.tests', [
            'tests' => $paginator, 'quizzes' => collect([$this->CreateAttempt()->quiz]),
            'quiz_slug' => 'addition', 'header' => __('quiz-management.preview.title'),
            'perPage' => 10, 'perPageOptions' => [10, 20, 50, 100],
        ]);
    }

    private function ReadDocument(string $html): \DOMXPath
    {
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
