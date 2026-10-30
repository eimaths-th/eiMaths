<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Department;
use App\Models\GuestQuizzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ElearningMetaPixelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        config(['services.elearning_meta_pixel.enabled' => true,
            'services.elearning_meta_pixel.id' => '1612378803966693']);
    }

    private function registration(): array
    {
        return ['first_name' => 'Test', 'last_name' => 'Only', 'grade' => 'P1',
            'email' => 'pixel-test@example.test', 'phone' => '0000000000'];
    }

    private function guest(): GuestQuizzer
    {
        return GuestQuizzer::create($this->registration() + [
            'password' => bcrypt('test-password'), 'expires_at' => now()->addDays(10),
        ]);
    }

    public function test_public_pages_render_only_on_the_production_host(): void
    {
        $this->get('https://app.eimaths-th.com/students-login')->assertOk()
            ->assertSee('const pixelId = "1612378803966693";', false)
            ->assertSee('const successEvent = null;', false);
        $this->get('https://app.eimaths-th.com/free-quiz/register')->assertOk()
            ->assertSee('const registrationPage = true;', false);
        foreach (['http://localhost', 'https://test-web.eimaths-th.com', 'https://app.eimaths-th.com.example.org'] as $host) {
            $this->get($host.'/students-login')->assertOk()
                ->assertDontSee('connect.facebook.net/en_US/fbevents.js', false);
        }
        config(['services.elearning_meta_pixel.enabled' => false]);
        $this->get('https://app.eimaths-th.com/students-login')->assertOk()
            ->assertDontSee('connect.facebook.net/en_US/fbevents.js', false);
        config(['services.elearning_meta_pixel.enabled' => true, 'services.elearning_meta_pixel.id' => 'invalid']);
        $this->get('https://app.eimaths-th.com/students-login')->assertOk()
            ->assertDontSee('connect.facebook.net/en_US/fbevents.js', false);
    }

    public function test_registration_emits_success_once_after_redirect(): void
    {
        $this->post('https://app.eimaths-th.com/free-quiz/register', $this->registration())
            ->assertRedirect()->assertSessionHas('elearning_meta_event', 'CompleteRegistration');
        $this->get('https://app.eimaths-th.com/free-quiz/home')->assertOk()
            ->assertSee('const successEvent = "CompleteRegistration";', false);
        $this->get('https://app.eimaths-th.com/free-quiz/home')->assertOk()
            ->assertDontSee('connect.facebook.net/en_US/fbevents.js', false);
    }

    public function test_invalid_and_duplicate_registrations_do_not_queue_success(): void
    {
        $this->post('/free-quiz/register', [])->assertSessionHasErrors()
            ->assertSessionMissing('elearning_meta_event');
        $this->guest();
        $this->post('/free-quiz/register', $this->registration())->assertSessionHasErrors('email')
            ->assertSessionMissing('elearning_meta_event');
    }

    public function test_guest_login_tracks_success_but_not_bad_password_or_expired_account(): void
    {
        $guest = $this->guest();
        $credentials = ['guest_email' => $guest->email, 'guest_password' => 'test-password'];
        $this->post('/free-quiz/login', array_replace($credentials, ['guest_password' => 'wrong']))
            ->assertSessionHasErrors()->assertSessionMissing('elearning_meta_event');
        $this->post('https://app.eimaths-th.com/free-quiz/login', $credentials)
            ->assertRedirect()->assertSessionHas('elearning_meta_event', 'ELearningLogin');
        $this->get('https://app.eimaths-th.com/free-quiz/home')->assertOk()
            ->assertSee('const successEvent = "ELearningLogin";', false);
        $this->get('https://app.eimaths-th.com/free-quiz/home')->assertOk()
            ->assertDontSee('connect.facebook.net/en_US/fbevents.js', false);
        Auth::guard('guest_quiz')->logout();
        $guest->update(['expires_at' => now()->subDay()]);
        $this->post('/free-quiz/login', $credentials)->assertRedirect(route('guest.quiz.expired'))
            ->assertSessionMissing('elearning_meta_event');
    }

    public function test_student_login_tracks_success_once_and_rejects_bad_password(): void
    {
        Department::forceCreate(['id' => 2, 'name' => 'Test branch', 'code' => 'TEST', 'status' => 1]);
        Contact::create(['centre' => 2, 'name' => 'Test student', 'code' => 'PIXEL001',
            'type' => 'student', 'password' => bcrypt('test-password')]);
        $this->post('/students-login-check', ['code' => 'PIXEL001', 'password' => 'wrong'])
            ->assertSessionHasErrors()->assertSessionMissing('elearning_meta_event');
        $this->post('https://app.eimaths-th.com/students-login-check', ['code' => 'PIXEL001', 'password' => 'test-password'])
            ->assertRedirect()->assertSessionHas('elearning_meta_event', 'ELearningLogin');
        $this->get('https://app.eimaths-th.com/quiz-home')->assertOk()
            ->assertSee('const successEvent = "ELearningLogin";', false);
        $this->get('https://app.eimaths-th.com/quiz-home')->assertOk()
            ->assertDontSee('connect.facebook.net/en_US/fbevents.js', false);
    }
}
