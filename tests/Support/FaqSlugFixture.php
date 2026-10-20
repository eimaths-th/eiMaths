<?php

namespace Tests\Support;

use App\Models\Department;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

trait FaqSlugFixture
{
    private function Attributes(array $overrides = []): array
    {
        return array_replace([
            'category_id' => FaqCategory::query()->firstOrFail()->id,
            'question' => 'Primary question',
            'question_en' => 'English question',
            'answer' => 'Answer',
            'answer_en' => 'English answer',
            'position' => 0,
            'is_published' => true,
        ], $overrides);
    }

    private function Editor(): User
    {
        $department = Department::find(Department::HQ_ID);
        if ($department === null) {
            Department::forceCreate(['id' => Department::HQ_ID, 'name' => 'FAQ Test HQ',
                'code' => 'FAQ-TEST-HQ', 'status' => 1, 'enabled_modules' => null]);
        } else {
            $department->update(['status' => 1, 'enabled_modules' => null]);
        }
        $editor = User::factory()->create(['department_id' => Department::HQ_ID,
            'disable' => 0, 'password' => Hash::make('test-only-password')]);
        foreach (['faq-create', 'faq-edit'] as $permission_name) {
            $editor->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }
        return $editor;
    }

    private function CurrentSlug(Faq $faq, string $locale): string
    {
        return (string) \Illuminate\Support\Facades\DB::table('faq_slug_routes')
            ->where('faq_id', $faq->id)->where('locale', $locale)
            ->where('current_marker', 1)->value('slug');
    }
}
