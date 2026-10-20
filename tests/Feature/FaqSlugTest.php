<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class FaqSlugTest extends TestCase
{
    use RefreshDatabase;

    private function Actor(): User
    {
        $department = Department::find(Department::HQ_ID);
        if ($department === null) {
            $department = Department::forceCreate(['id' => Department::HQ_ID, 'name' => 'FAQ Test HQ',
                'code' => 'FAQ-TEST-HQ', 'status' => 1, 'enabled_modules' => null]);
        } else {
            $department->update(['status' => 1, 'enabled_modules' => null]);
        }
        $actor = User::factory()->create(['department_id' => $department->id, 'disable' => 0, 'password' => \Illuminate\Support\Facades\Hash::make('test-only-password')]);
        foreach (['faq-create', 'faq-edit'] as $permission_name) {
            $actor->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }
        return $actor;
    }

    private function Attributes(array $overrides = []): array
    {
        return array_replace(['category_id' => FaqCategory::query()->firstOrFail()->id,
            'question' => 'Primary question', 'question_en' => 'How does CPA work?',
            'answer' => 'Answer', 'position' => 1, 'is_published' => true], $overrides);
    }

    #[Test]
    public function SchemaAddsSlugs(): void
    {
        $this->assertTrue(Schema::hasColumn('faqs', 'slug'));
    }

    #[Test]
    public function ManagementCreationAssignsStableSlugs(): void
    {
        $this->actingAs($this->Actor());
        $this->post('/faqs', $this->Attributes())->assertRedirect(route('faqs.index'));
        $faq = Faq::query()->latest('id')->firstOrFail();
        $this->assertSame('how-does-cpa-work', $faq->slug);
        $this->post('/faqs', $this->Attributes())->assertRedirect(route('faqs.index'));
        $second_faq = Faq::query()->latest('id')->firstOrFail();
        $this->assertSame('how-does-cpa-work-'.$second_faq->id, $second_faq->slug);
        $this->put('/faqs/'.$faq->id, $this->Attributes([
            'question' => 'Changed primary question', 'question_en' => 'Changed English question',
            'is_published' => false,
        ]))->assertRedirect(route('faqs.index'));
        $this->assertSame('how-does-cpa-work', $faq->refresh()->slug);
    }

    #[Test]
    public function BackfillPreservesContentTimestampsAndExistingSlugs(): void
    {
        $faq = Faq::create($this->Attributes(['question' => '?!', 'question_en' => null]));
        $before = (array) \Illuminate\Support\Facades\DB::table('faqs')->find($faq->id);
        $this->assertArrayHasKey('faq:backfill-slugs', \Illuminate\Support\Facades\Artisan::all());
        $this->artisan('faq:backfill-slugs')->assertExitCode(0);
        $after = (array) \Illuminate\Support\Facades\DB::table('faqs')->find($faq->id);
        $this->assertSame('faq-'.$faq->id, $after['slug']);
        unset($before['slug'], $after['slug']);
        $this->assertSame($before, $after);
        $slugs = \Illuminate\Support\Facades\DB::table('faqs')->orderBy('id')->pluck('slug', 'id')->all();
        $this->artisan('faq:backfill-slugs')->assertExitCode(0);
        $this->assertSame($slugs, \Illuminate\Support\Facades\DB::table('faqs')->orderBy('id')->pluck('slug', 'id')->all());
    }

    #[Test]
    public function SlugBoundariesAndNestedCollisionsStayUnique(): void
    {
        $writer = app(\App\Services\Faq\FaqSlugWriter::class);
        $unicode_faq = $writer->Create($this->Attributes(['question_en' => null, 'question' => "\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21} !"]));
        $this->assertSame("\u{0E04}\u{0E33}\u{0E16}\u{0E32}\u{0E21}", $unicode_faq->slug);
        $long_faq = $writer->Create($this->Attributes(['question_en' => str_repeat('a', 121)]));
        $this->assertSame(str_repeat('a', 120), $long_faq->slug);
        $first = $writer->Create($this->Attributes());
        $pending = Faq::create($this->Attributes());
        $blocker = Faq::create($this->Attributes());
        \Illuminate\Support\Facades\DB::table('faqs')->where('id', $blocker->id)->update(['slug' => 'how-does-cpa-work-'.$pending->id]);
        $this->assertSame('how-does-cpa-work-'.$pending->id.'-1', $writer->Assign($pending->id));
        $this->assertSame('how-does-cpa-work', $first->slug);
    }

    #[Test]
    public function SlugWriteFailureRollsBackCreation(): void
    {
        $before_count = Faq::count();
        \Illuminate\Support\Facades\DB::unprepared("CREATE TRIGGER faq_slug_test_failure BEFORE UPDATE OF slug ON faqs BEGIN SELECT RAISE(ABORT, 'test-only slug failure'); END");
        try {
            try {
                app(\App\Services\Faq\FaqSlugWriter::class)->Create($this->Attributes());
                $this->fail('The injected database failure must propagate.');
            } catch (\Illuminate\Database\QueryException $exception) {
                $this->assertSame($before_count, Faq::count());
            }
        } finally {
            \Illuminate\Support\Facades\DB::unprepared('DROP TRIGGER faq_slug_test_failure');
        }
    }
}
