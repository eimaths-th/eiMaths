<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UiLocalizedPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Department::forceCreate([
            'id' => 2,
            'name' => 'Locale Test Branch',
            'code' => 'LOCALE',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $role = Role::findOrCreate('BranchAdmin', 'web');
        foreach ([
            'student-list',
            'student-edit',
            'student-delete',
            'history-list',
            'parent-list',
            'guest-report-list',
            'eiform-list',
            'quiz-list',
        ] as $permission) {
            $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $this->actor = User::factory()->create([
            'department_id' => $branch->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);
    }

    public static function Locales(): array
    {
        return [
            ['en', 'eiMaths Dashboard'],
            ['th', 'ภาพรวม eiMaths'],
        ];
    }

    #[Test]
    public function SharedShellHasAnAccessibleLanguageSelect(): void
    {
        $this->actor->update(['language_code' => 'th']);

        $response = $this->actingAs($this->actor, 'web')->get(route('home'));
        $response->assertOk()
            ->assertSee('<html lang="th">', false)
            ->assertSee('class="ui-language-switcher"', false)
            ->assertSee('action="' . route('locale.update') . '"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('id="ui-language-select"', false)
            ->assertSee('name="locale"', false)
            ->assertSee('value="en"', false)
            ->assertSee('value="th"', false)
            ->assertSee('English')
            ->assertSee('ไทย')
            ->assertSee('ภาษาที่แสดง')
            ->assertSee('Locale Test Branch');

        $document = new DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new DOMXPath($document);
        $selected_option = $xpath->query('//*[@id="ui-language-select"]/option[@selected]')->item(0);

        $this->assertNotNull($selected_option);
        $this->assertSame('th', $selected_option->getAttribute('value'));
    }

    #[Test]
    public function LanguageSelectIsRenderedAfterTheNotificationControl(): void
    {
        $this->actor->givePermissionTo(Permission::findOrCreate('book-shipment-receive', 'web'));

        $content = $this->actingAs($this->actor, 'web')->get(route('home'))->getContent();
        $notification_position = strpos($content, 'data-pending-work-alert-toggle');
        $language_select_position = strpos($content, 'id="ui-language-select"');

        $this->assertNotFalse($notification_position);
        $this->assertNotFalse($language_select_position);
        $this->assertLessThan($language_select_position, $notification_position);
    }

    #[Test]
    #[DataProvider('Locales')]
    public function DashboardRendersInBothLocales(string $locale, string $heading): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('home'));
        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertSee('Locale Test Branch')
            ->assertDontSee('dashboard.title')
            ->assertDontSee('navigation.menu.dashboard');

        $this->AssertContentHeading($response->getContent(), $heading);
    }

    public static function StudentPages(): array
    {
        $pages = [
            ['contacts', 'Student List', 'รายชื่อนักเรียน'],
            ['demo.list', 'Demo Student List', 'รายชื่อนักเรียนทดลอง'],
            ['histories.index', 'History List', 'ประวัติการเรียน'],
            ['discontinued', 'Discontinued Student', 'นักเรียนที่ไม่ต่อคลาส'],
            ['contacts.graduated', 'Graduated Student List', 'นักเรียนที่จบหลักสูตร'],
            ['parent.index', 'Parent List', 'รายชื่อผู้ปกครอง'],
            ['guest.report', 'Guest Quizzers List', 'รายชื่อผู้ทดลองทำแบบทดสอบ'],
            ['ei_form.backEndIndex', 'eiForm', 'ใบสมัคร eiForm'],
            ['generate.student', 'Generate Student Password', 'สร้างรหัสผ่านนักเรียน'],
        ];
        $cases = [];
        foreach ($pages as [$route_name, $english_heading, $thai_heading]) {
            $cases[] = [$route_name, 'en', $english_heading];
            $cases[] = [$route_name, 'th', $thai_heading];
        }

        return $cases;
    }

    #[Test]
    #[DataProvider('StudentPages')]
    public function StudentsManagementPagesRenderInBothLocales(
        string $route_name,
        string $locale,
        string $heading
    ): void {
        $this->actor->update(['language_code' => $locale]);
        if ($route_name === 'demo.list') {
            config(['demo-students.enabled' => true]);
        }

        $page_actor = $this->StudentPageActor($route_name);
        $response = $this->actingAs($page_actor, 'web')->get(route($route_name));
        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertDontSee('students.titles.');

        $this->AssertContentHeading($response->getContent(), $heading);
    }

    public static function StudentLoginLabels(): array
    {
        return [
            [
                'en',
                ['Student Login', 'Student Code', 'Remember Me', 'Sign In', 'Free Trial', 'Guest Login'],
            ],
            [
                'th',
                ['เข้าสู่ระบบนักเรียน', 'รหัสนักเรียน', 'จดจำฉัน', 'เข้าสู่ระบบ', 'ทดลองใช้ฟรี', 'เข้าสู่ระบบผู้ทดลอง'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('StudentLoginLabels')]
    public function StudentLoginRendersInBothLocales(string $locale, array $labels): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('student.login'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertDontSee('students.login.');
        foreach ($labels as $label) {
            $response->assertSee($label);
        }
    }

    public static function StudentLoginValidationMessages(): array
    {
        return [
            ['en', 'Student code is required.', 'Password is required.'],
            ['th', 'กรุณากรอกรหัสนักเรียน', 'กรุณากรอกรหัสผ่าน'],
        ];
    }

    #[Test]
    #[DataProvider('StudentLoginValidationMessages')]
    public function StudentLoginValidationUsesTheSelectedLocale(
        string $locale,
        string $code_message,
        string $password_message
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')
            ->from(route('student.login'))
            ->post(route('student.check'), []);

        $response->assertRedirect(route('student.login'))
            ->assertSessionHasErrors([
                'code' => $code_message,
                'password' => $password_message,
            ]);
    }

    public static function GuestLoginValidationMessages(): array
    {
        return [
            ['en', 'First name is required.'],
            ['th', 'กรุณากรอกชื่อ'],
        ];
    }

    #[Test]
    #[DataProvider('GuestLoginValidationMessages')]
    public function GuestRegistrationValidationUsesTheSelectedLocale(string $locale, string $first_name_message): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')
            ->from(route('student.login'))
            ->post(route('guest.quiz.register.store'), []);

        $response->assertRedirect(route('student.login'))
            ->assertSessionHasErrors(['first_name' => $first_name_message]);
    }

    public static function CourseWebsiteLabels(): array
    {
        return [
            [
                'en',
                ['Mathematics Courses', 'All Courses', 'Search courses or levels', 'Available', 'Confirm course booking'],
            ],
            [
                'th',
                ['คอร์สเรียนคณิตศาสตร์', 'ทั้งหมด', 'ค้นหาชื่อคอร์ส หรือระดับชั้น...', 'พร้อมให้เรียน', 'ยืนยันการจองคอร์สเรียน'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('CourseWebsiteLabels')]
    public function CourseWebsiteRendersInBothLocales(string $locale, array $labels): void
    {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('affiliate.courses'));

        $response->assertOk()
            ->assertSee('<html dir="" class="" lang="' . $locale . '"', false)
            ->assertDontSee('course-website.');

        foreach ($labels as $label) {
            $response->assertSee($label);
        }
    }

    public static function QuizManagementPages(): array
    {
        $pages = [
            ['home3', 'eiMaths Quiz Platform', 'แพลตฟอร์มแบบทดสอบ eiMaths'],
            ['questions', 'Question List', 'รายการข้อสอบ'],
            ['quizzes', 'Quiz List', 'รายการแบบทดสอบ'],
            ['questions-quizzes.create', 'Quiz Builder', 'สร้างแบบทดสอบ'],
            ['tests', 'Test Preview', 'ดูตัวอย่างผลการทดสอบ'],
            ['assign', 'Assigned List', 'รายการกำหนดแบบทดสอบ'],
            ['ts.questions.index', 'Mathematical Symbol Tool', 'เครื่องมือสัญลักษณ์ทางคณิตศาสตร์'],
            ['ts.questions.create', 'Create System Question', 'สร้างคำถามระบบ'],
            ['ts.questions.view', 'System Question Preview', 'ตัวอย่างคำถามระบบ'],
        ];
        $cases = [];
        foreach ($pages as [$route_name, $english_heading, $thai_heading]) {
            $cases[] = [$route_name, 'en', $english_heading];
            $cases[] = [$route_name, 'th', $thai_heading];
        }

        return $cases;
    }

    #[Test]
    #[DataProvider('QuizManagementPages')]
    public function QuizManagementPagesRenderInBothLocales(
        string $route_name,
        string $locale,
        string $heading
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route($route_name));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($heading)
            ->assertDontSee('quiz-management.');
    }

    public static function SecondaryPageLabels(): array
    {
        return [
            ['discontinued', 'th', 'เริ่มเรียนต่อทั้งหมด'],
            ['contacts.graduated', 'th', 'วันที่เริ่ม'],
            ['parent.index', 'th', 'สร้างผู้ปกครอง'],
            ['guest.report', 'th', 'ชั้นเรียน'],
            ['ei_form.backEndIndex', 'th', 'ชื่อผู้ปกครอง'],
        ];
    }

    #[Test]
    #[DataProvider('SecondaryPageLabels')]
    public function SecondaryStudentPagesExposeLocalizedLabels(
        string $route_name,
        string $locale,
        string $expected_label
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $page_actor = $this->StudentPageActor($route_name);
        $response = $this->actingAs($page_actor, 'web')->get(route($route_name));

        $response->assertOk()->assertSee($expected_label);
    }

    private function StudentPageActor(string $route_name): User
    {
        if ($route_name !== 'ei_form.backEndIndex') {
            return $this->actor;
        }

        $hq = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Locale Test HQ',
            'code' => 'HQ',
            'status' => 1,
        ]);
        $role = Role::findOrCreate('Admin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('student-list', 'web'));
        $central_actor = User::factory()->create([
            'department_id' => $hq->id,
            'disable' => 0,
            'language_code' => $this->actor->language_code,
            'password' => Hash::make('secret'),
        ]);
        $central_actor->assignRole($role);

        return $central_actor;
    }

    private function AssertContentHeading(string $html, string $expected_heading): void
    {
        $document = new DOMDocument();
        $previous_errors = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8" ?>' . $html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous_errors);
        }

        $xpath = new DOMXPath($document);
        $heading_nodes = $xpath->query(
            '//div[contains(concat(" ", normalize-space(@class), " "), " content-wrapper ")]//h3'
        );
        $headings = [];
        foreach ($heading_nodes as $heading_node) {
            $headings[] = trim(preg_replace('/\s+/u', ' ', $heading_node->textContent));
        }

        $this->assertContains($expected_heading, $headings);
    }
}
