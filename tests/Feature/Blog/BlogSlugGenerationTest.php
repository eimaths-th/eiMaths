<?php

namespace Tests\Feature\Blog;

use App\Models\Blog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BlogSlugGenerationTest extends TestCase
{
    use RefreshDatabase;

    private const COLLIDING_SLUG = 'ทำไมต้องเรียนคณิตศาสตร์-';

    private const COLLISION_TARGET_SLUG = 'ทำไมต้องเรียนคณิตศาสตร์';

    private function CreateAuthor(string $permission_name): User
    {
        $department = Department::forceCreate(['id' => Department::HQ_ID, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        $author = User::factory()->create([
            'department_id' => $department->id,
            'language_code' => 'en',
            'disable' => 0,
            'password' => Hash::make('test-password'),
        ]);
        $author->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));

        return $author;
    }

    private function CreateBlog(string $slug): Blog
    {
        return Blog::create([
            'title' => 'Title of '.$slug,
            'slug' => $slug,
            'content' => '<p>Body</p>',
            'is_published' => false,
        ]);
    }

    public static function TitlesWithEdgePunctuation(): array
    {
        return [
            'trailing question mark' => ['ทำไมต้องเรียน ?', 'ทำไมต้องเรียน'],
            'leading and trailing punctuation' => ['? Number Sense ?', 'number-sense'],
        ];
    }

    public static function TitlesWithoutSlugCharacters(): array
    {
        return [
            'question marks' => ['???'],
            'spaced punctuation' => ['? ?'],
            'emoji only' => ['🎉 🎉'],
        ];
    }

    #[Test]
    #[DataProvider('TitlesWithEdgePunctuation')]
    public function RemovesEdgeDashesFromGeneratedSlugs(string $blog_title, string $expected_slug): void
    {
        $this->actingAs($this->CreateAuthor('blog-create'))
            ->post(route('blogs.store'), ['title' => $blog_title, 'content' => '<p>Body</p>'])
            ->assertRedirect(route('blogs.index'));

        $this->assertSame([$expected_slug], Blog::query()->pluck('slug')->all());
    }

    #[Test]
    #[DataProvider('TitlesWithoutSlugCharacters')]
    public function RejectsNewPostsThatWouldHaveAnEmptySlug(string $blog_title): void
    {
        $this->actingAs($this->CreateAuthor('blog-create'))
            ->post(route('blogs.store'), ['title' => $blog_title, 'content' => '<p>Body</p>'])
            ->assertSessionHas('error');

        $this->assertSame(0, Blog::query()->count());
    }

    #[Test]
    public function RejectsAnEditThatWouldEmptyTheSlug(): void
    {
        $blog = $this->CreateBlog('clean-post');

        $this->actingAs($this->CreateAuthor('blog-edit'))
            ->put(route('blogs.update', $blog), ['title' => '???', 'slug' => '-', 'content' => '<p>Body</p>'])
            ->assertSessionHas('error');

        $this->assertSame('clean-post', $blog->fresh()->slug);
    }

    #[Test]
    public function EditingKeepsAStoredSlugThatWasNotChanged(): void
    {
        $colliding_blog = $this->CreateBlog(self::COLLIDING_SLUG);
        $this->CreateBlog(self::COLLISION_TARGET_SLUG);

        $this->actingAs($this->CreateAuthor('blog-edit'))
            ->put(route('blogs.update', $colliding_blog), [
                'title' => 'Edited title',
                'slug' => self::COLLIDING_SLUG,
                'content' => '<p>Edited body</p>',
            ])
            ->assertRedirect();

        $edited_blog = $colliding_blog->fresh();
        $this->assertSame(self::COLLIDING_SLUG, $edited_blog->slug);
        $this->assertSame('Edited title', $edited_blog->title);
    }

    #[Test]
    public function EditingTrimsASlugChangedByTheAuthor(): void
    {
        $blog = $this->CreateBlog('clean-post');

        $this->actingAs($this->CreateAuthor('blog-edit'))
            ->put(route('blogs.update', $blog), [
                'title' => 'Edited title',
                'slug' => 'renamed-post-',
                'content' => '<p>Edited body</p>',
            ])
            ->assertRedirect();

        $this->assertSame('renamed-post', $blog->fresh()->slug);
    }
}
