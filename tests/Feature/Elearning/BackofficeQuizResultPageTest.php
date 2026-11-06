<?php

namespace Tests\Feature\Elearning;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\QuizResultFixtures;
use Tests\TestCase;

class BackofficeQuizResultPageTest extends TestCase
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
    public function RendersStaffShellAndOnlyPermittedAnswerLinks(): void
    {
        $participant = $this->CreateResultActor();
        $attempt = $this->CreateResultAttempt('user', $participant->id);
        $actor = $this->CreateResultActor(['quiz-results-list']);
        $this->actingAs($actor)->get('/backoffice/quiz-results')->assertOk()
            ->assertSee('main-sidebar', false)->assertSee('Addition practice')
            ->assertDontSee('href="'.route('backoffice.quiz-results.show', $attempt).'"', false);
        $actor = $this->CreateResultActor(['quiz-results-list', 'quiz-results-view']);
        $this->actingAs($actor)->get('/backoffice/quiz-results')->assertOk()
            ->assertSee('href="'.route('backoffice.quiz-results.show', $attempt).'"', false);
        $this->get('/backoffice/quiz-results/attempts/42')->assertOk()->assertSee('main-sidebar', false)
            ->assertSee('One plus one?')->assertDontSee("addEventListener('beforeunload'", false);
    }

    #[Test]
    public function RendersViewOnlyDetailWithoutForbiddenListLink(): void
    {
        $participant = $this->CreateResultActor();
        $this->CreateResultAttempt('user', $participant->id);
        $actor = $this->CreateResultActor(['quiz-results-view']);
        $this->actingAs($actor)->get('/backoffice/quiz-results/attempts/42')->assertOk()
            ->assertSee('One plus one?')->assertDontSee('href="'.route('backoffice.quiz-results.index').'"', false);
    }

    #[Test]
    public function ShowsResultMenuIndependentlyOfQuizManagement(): void
    {
        $actor = $this->CreateResultActor(['quiz-results-list']);
        $response = $this->actingAs($actor)->get('/backoffice/quiz-results');
        $response->assertOk()->assertSee('href="'.route('backoffice.quiz-results.index').'"', false);
        $document = new \DOMDocument;
        $previous_error_mode = libxml_use_internal_errors(true);
        try {
            $document->loadHTML($response->getContent());
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous_error_mode);
        }
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//aside//a[@href="'.route('backoffice.quiz-results.index').'" and contains(@class,"active")]')->length);
        $actor = $this->CreateResultActor(['quiz-manage']);
        $this->actingAs($actor);
        $this->view('layouts.menu', ['book_shipment_alert_role' => null])->assertDontSee('href="'.route('backoffice.quiz-results.index').'"', false);
        $this->view('layouts.nav-bar')->assertDontSee('href="'.route('tests').'"', false);
    }

    #[Test]
    public function HandlesMissingQuizAndZeroQuestionsAndExcludesMissingParticipants(): void
    {
        $actor = $this->CreateResultActor(['quiz-results-list']);
        $attempt = $this->CreateResultAttempt('user', $actor->id);
        $attempt->answers()->delete();
        $attempt->update(['quiz_id' => null, 'result' => 0]);
        $this->actingAs($actor)->get('/backoffice/quiz-results')->assertOk()
            ->assertSee('0/0')->assertSee(__('quiz-management.preview.not_available'))
            ->assertViewHas('tests', fn ($attempts) => $attempts->total() === 1);
        $attempt->update(['type_user' => 'student']);
        $this->get('/backoffice/quiz-results')->assertOk()
            ->assertViewHas('tests', fn ($attempts) => $attempts->total() === 0)
            ->assertSee(__('quiz-management.preview.no_tests'));
    }

    #[Test]
    public function KeepsBackofficeCardsEscapedAndHandlesEmptyAndZeroValues(): void
    {
        $actor = $this->CreateResultActor(['quiz-results-list']);
        $this->actingAs($actor)->get('/backoffice/quiz-results')->assertOk()->assertSee(__('quiz-management.preview.no_tests'));
        $attempt = $this->CreateResultAttempt('user', $actor->id);
        $attempt->update(['result' => 0, 'time_spent' => 0]);
        $attempt->quiz->update(['title' => str_repeat('แบบทดสอบ Long title ', 10).'<script>alert(1)</script>']);
        $this->get('/backoffice/quiz-results')->assertOk()->assertSee('0/1')->assertSee('00:00:00')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }
}
