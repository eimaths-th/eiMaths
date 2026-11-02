<?php

namespace Tests\Feature\Elearning;

use App\Models\{Answer, Contact, FactionAnswer, ImageOption, Option, Question, Quiz, Test as QuizTest};
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\{DataProvider, Test};
use Tests\TestCase;

class ResultPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    #[Test]
    public function RendersZeroScoreAndDuration(): void
    {
        $this->RenderResult([], ['result' => 0, 'time_spent' => 0])
            ->assertSee('0/2')->assertSee('0.00%')->assertSee('0 วินาที');
    }

    #[Test]
    public function RendersAnEmptyQuizWithoutDivision(): void
    {
        $this->RenderResult([], ['time_spent' => null], 0)
            ->assertSee('0/0')->assertSee('—')->assertDontSee('NaN')->assertDontSee('INF');
    }

    #[Test]
    public function FormatsDurationWithoutChangingStoredSeconds(): void
    {
        $html = (string) $this->RenderResult([], ['time_spent' => 3661]);
        $this->assertStringContainsString('1 ชั่วโมง 1 นาที 1 วินาที', preg_replace('/\s+/u', ' ', strip_tags($html)));
        $this->RenderResult([], ['time_spent' => null])->assertSee('—');
    }

    public static function AnswerTypes(): array
    {
        return [['options'], ['image'], ['written'], ['fraction-options'], ['fraction-written']];
    }

    #[Test, DataProvider('AnswerTypes')]
    public function RendersSelectedAndCorrectAnswers(string $answer_type): void
    {
        $answer = $this->CreateAnswer($answer_type);
        $rendered = $this->RenderResult([$answer]);
        $rendered->assertSee('คำตอบของคุณ')->assertSee('คำตอบที่ถูก')->assertSee('ตอบไม่ถูก');
        if ($answer_type === 'image') {
            $rendered->assertSee('selected.png')->assertSee('correct.png');
        } elseif ($answer_type === 'fraction-options') {
            $rendered->assertSee('frac{1}{3}')->assertSee('frac{1}{2}');
        } elseif ($answer_type === 'fraction-written') {
            $rendered->assertSee('0')->assertSee('frac{1}{2}');
        } else {
            $rendered->assertSee('0')->assertSee('4');
        }
        $answer->correct = true;
        $this->RenderResult([$answer])->assertSee('ตอบถูก')->assertDontSee('ตอบไม่ถูก');
    }

    #[Test, DataProvider('AnswerTypes')]
    public function DistinguishesUnansweredFromZero(string $answer_type): void
    {
        $answer = $this->CreateAnswer($answer_type);
        $this->RenderResult([$answer])->assertDontSee('ยังไม่ได้ตอบ');
        $answer->option_id = null;
        $answer->image_id = null;
        $answer->written_answer = null;
        $answer->fraction = null;
        $this->RenderResult([$answer])->assertSee('ยังไม่ได้ตอบ')->assertDontSee('ตอบไม่ถูก');
        if (in_array($answer_type, ['written', 'fraction-written'], true)) {
            $answer->written_answer = '';
            $answer->fraction = '';
            $this->RenderResult([$answer])->assertSee('ยังไม่ได้ตอบ');
        }
    }

    #[Test]
    public function PreservesEveryCorrectOption(): void
    {
        $answer = $this->CreateAnswer('options');
        $answer->question->options->push((new Option)->forceFill(['id' => 3, 'text' => 'another correct answer', 'correct' => 1]));
        $this->RenderResult([$answer])->assertSee('4')->assertSee('another correct answer');
    }

    #[Test]
    public function KeepsExplanationTextEscapedAndImagesInline(): void
    {
        $answer = $this->CreateAnswer('written');
        $answer->question->answer_explanation = '<script>alert(1)</script>';
        $answer->question->answer_explanation_image = 'explanation.png';
        $this->RenderResult([$answer])->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)->assertSee('img_questions/explanation.png')
            ->assertSee('ภาพอธิบายคำตอบข้อ 1');
    }

    #[Test]
    public function OmitsUnauthorizedVideoUrls(): void
    {
        $answer = $this->CreateAnswer('written');
        $answer->question->answer_explanation_video_url = 'https://drive.google.com/file/d/result-video/view';
        $this->RenderResult([$answer], [], 2, false)->assertDontSee('https://drive.google.com/file/d/result-video/preview')->assertDontSee('<iframe', false);
        $html = (string) $this->RenderResult([$answer], [], 2, true);
        $this->assertStringContainsString('https://drive.google.com/file/d/result-video/preview', $html);
        $this->assertMatchesRegularExpression('/<details\b[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<details\b[^>]*\bopen\b/', $html);
    }

    private function RenderResult(array $answers, array $attributes = [], int $question_count = 2, bool $can_view_videos = false): \Illuminate\Testing\TestView
    {
        $quiz = new Quiz(['title' => 'Addition practice']);
        $quiz->setRelation('questions', new Collection(array_fill(0, $question_count, new Question)));
        $test = new QuizTest(array_merge(['result' => 0, 'time_spent' => 60, 'type_user' => 'student', 'created_at' => '2026-11-02 10:00:00'], $attributes));
        $test->setRelation('quiz', $quiz)->setRelation('student', new Contact(['nickname' => 'Learner', 'name' => 'Test Learner']));
        return $this->view('home.result', ['test' => $test, 'questions_count' => $question_count, 'results' => collect($answers), 'header' => 'Results', 'canViewSolutionVideos' => $can_view_videos]);
    }

    private function CreateAnswer(string $answer_type): Answer
    {
        $question = new Question(['type' => str_starts_with($answer_type, 'fraction') ? 'fraction' : $answer_type, 'text' => 'What is the answer?', 'written_answer' => '4']);
        $question->setRelation('options', new Collection([
            (new Option)->forceFill(['id' => 1, 'text' => '0', 'correct' => 0]),
            (new Option)->forceFill(['id' => 2, 'text' => '4', 'correct' => 1]),
        ]));
        $question->setRelation('images', new Collection([
            (new ImageOption)->forceFill(['id' => 1, 'img_name' => 'selected.png', 'correct' => 0]),
            (new ImageOption)->forceFill(['id' => 2, 'img_name' => 'correct.png', 'correct' => 1]),
        ]));
        $fraction_type = $answer_type === 'fraction-options' ? 'options' : 'written';
        $question->setRelation('fractions', new Collection([
            (new FactionAnswer)->forceFill(['id' => 2, 'type' => $fraction_type, 'answer_type' => 'fraction', 'numerator' => 1, 'denominator' => 2, 'correct' => 1]),
            (new FactionAnswer)->forceFill(['id' => 1, 'type' => $fraction_type, 'answer_type' => 'fraction', 'numerator' => 1, 'denominator' => 3, 'correct' => 0]),
        ]));
        return (new Answer(['correct' => false, 'option_id' => 1, 'image_id' => 1, 'written_answer' => '0', 'fraction' => $answer_type === 'fraction-options' ? '1' : '0']))->setRelation('question', $question);
    }
}
