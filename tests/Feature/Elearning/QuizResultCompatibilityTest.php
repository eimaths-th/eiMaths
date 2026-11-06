<?php

namespace Tests\Feature\Elearning;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Quiz, Test as QuizTest};
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\QuizResultFixtures;
use Tests\TestCase;

class QuizResultCompatibilityTest extends TestCase
{
    use RefreshDatabase, QuizResultFixtures {
        QuizResultFixtures::beforeRefreshingDatabase insteadof RefreshDatabase;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->PrepareResultFixtures();
    }

    #[Test]
    public function PreservesOnlySupportedLegacyParameters(): void
    {
        $actor = $this->CreateResultActor(['quiz-results-list']);
        $this->actingAs($actor)->get('/tests/addition?page=2&per_page=10&redirect=https://example.test')
            ->assertRedirect(route('backoffice.quiz-results.index', ['quiz_slug' => 'addition', 'per_page' => 10, 'page' => 2]));
        $this->get('/tests?per_page=999&page=-2')->assertRedirect(route('backoffice.quiz-results.index', ['per_page' => 20]));
        $this->get('/tests')->assertRedirect(route('backoffice.quiz-results.index'));
    }

    #[Test]
    public function GuardsLegacyListAndStaffFilterWithoutChangingLeaderboard(): void
    {
        $actor = $this->CreateResultActor();
        $this->actingAs($actor)->get('/tests')->assertForbidden();
        foreach (['tests', 'backoffice.quiz-results.index'] as $route_name) {
            $this->post('/submit-leaderboard', ['route' => $route_name, 'quiz_slug' => 'addition'])->assertForbidden();
        }
        $this->post('/submit-leaderboard', ['route' => 'leaderboard', 'quiz_slug' => 'addition'])->assertRedirect(route('leaderboard', 'addition'));
        $actor = $this->CreateResultActor(['quiz-results-list']);
        $this->actingAs($actor)->post('/submit-leaderboard', ['route' => 'tests', 'quiz_slug' => 'addition', 'page' => 9])
            ->assertRedirect(route('backoffice.quiz-results.index', 'addition'));
        $this->post('/submit-leaderboard', ['route' => 'backoffice.quiz-results.index', 'quiz_slug' => '0'])
            ->assertRedirect(route('backoffice.quiz-results.index'));
        $this->postJson('/submit-leaderboard', ['route' => 'tests'])->assertUnprocessable()->assertJsonValidationErrors('quiz_slug');
    }

    #[Test]
    public function PreservesFilterSelectionAndPageSize(): void
    {
        $actor = $this->CreateResultActor(['quiz-results-list']);
        $this->CreateResultAttempt('user', $actor->id);
        $response = $this->actingAs($actor)->get('/backoffice/quiz-results/addition?per_page=10');
        $response->assertOk()->assertViewHas('quiz_slug', 'addition')->assertViewHas('perPage', 10);
        $this->get('/backoffice/quiz-results?per_page=15')->assertViewHas('perPage', 20);
        $this->get('/backoffice/quiz-results?per_page[]=10')->assertViewHas('perPage', 20);
    }

    #[Test]
    public function PaginatesWithinTheSelectedQuizAndBranch(): void
    {
        $actor = $this->CreateResultActor(['quiz-results-list']);
        $attempt = $this->CreateResultAttempt('user', $actor->id);
        for ($attempt_number = 0; $attempt_number < 20; $attempt_number++) {
            $attempt->replicate()->save();
        }
        $other_quiz = Quiz::create(['slug' => 'subtraction', 'title' => 'Subtraction practice', 'level' => 'P1', 'term' => 1, 'section' => 2]);
        QuizTest::create(['type_user' => 'user', 'user_id' => $actor->id, 'quiz_id' => $other_quiz->id, 'result' => 0, 'time_spent' => 0]);
        $this->actingAs($actor)->get('/backoffice/quiz-results/addition?per_page=10&page=2')->assertOk()
            ->assertViewHas('tests', fn ($attempts) => $attempts->total() === 21 && $attempts->count() === 10 && $attempts->currentPage() === 2)
            ->assertSee('page=3', false)->assertDontSee('Subtraction practice</h2>', false);
    }
}
