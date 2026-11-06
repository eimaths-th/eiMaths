<?php

namespace Tests\Feature\Blog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NormalizeBlogEdgeDashSlugsMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const RENAMED_OLD_SLUG = 'combination-thinking-skill-';

    private const RENAMED_NEW_SLUG = 'combination-thinking-skill';

    private const UNPUBLISHED_SLUG = 'ทำไมต้องเรียนคณิตศาสตร์-';

    private const UNLISTED_SLUG = 'other-post-';

    private function CreatePublishedBlog(string $slug): void
    {
        DB::table('blogs')->insert([
            'title' => 'Title of '.$slug,
            'slug' => $slug,
            'content' => '<p>Body of '.$slug.'</p>',
            'is_published' => true,
        ]);
    }

    private function LoadMigration(): object
    {
        return require database_path('migrations/2026_11_06_000001_normalize_blog_edge_dash_slugs.php');
    }

    private function ReadBlog(string $slug): ?object
    {
        return DB::table('blogs')->where('slug', $slug)->first();
    }

    #[Test]
    public function RenamesListedSlugsAndLeavesUnlistedSlugsAlone(): void
    {
        $this->CreatePublishedBlog(self::RENAMED_OLD_SLUG);
        $this->CreatePublishedBlog(self::UNLISTED_SLUG);

        $this->LoadMigration()->up();

        $this->assertNull($this->ReadBlog(self::RENAMED_OLD_SLUG));
        $this->assertSame('<p>Body of '.self::RENAMED_OLD_SLUG.'</p>', $this->ReadBlog(self::RENAMED_NEW_SLUG)->content);
        $this->assertNotNull($this->ReadBlog(self::UNLISTED_SLUG));
    }

    #[Test]
    public function UnpublishesTheCollidingArticleWithoutRenamingOrDeletingIt(): void
    {
        $this->CreatePublishedBlog(self::UNPUBLISHED_SLUG);

        $this->LoadMigration()->up();

        $colliding_blog = $this->ReadBlog(self::UNPUBLISHED_SLUG);
        $this->assertSame(0, (int) $colliding_blog->is_published);
        $this->assertSame('<p>Body of '.self::UNPUBLISHED_SLUG.'</p>', $colliding_blog->content);
    }

    #[Test]
    public function SkipsARenameWhenTheTargetSlugAlreadyExists(): void
    {
        $this->CreatePublishedBlog(self::RENAMED_OLD_SLUG);
        $this->CreatePublishedBlog(self::RENAMED_NEW_SLUG);

        $this->LoadMigration()->up();

        $this->assertSame('<p>Body of '.self::RENAMED_OLD_SLUG.'</p>', $this->ReadBlog(self::RENAMED_OLD_SLUG)->content);
        $this->assertSame('<p>Body of '.self::RENAMED_NEW_SLUG.'</p>', $this->ReadBlog(self::RENAMED_NEW_SLUG)->content);
    }

    #[Test]
    public function SkipsARenameWhenTheTargetIsAnUnpublishedPostAndRollbackKeepsBothRows(): void
    {
        $this->CreatePublishedBlog(self::RENAMED_OLD_SLUG);
        DB::table('blogs')->insert([
            'title' => 'Draft target',
            'slug' => self::RENAMED_NEW_SLUG,
            'content' => '<p>Draft target</p>',
            'is_published' => false,
        ]);
        $migration = $this->LoadMigration();

        $migration->up();
        $migration->down();

        $this->assertSame('<p>Body of '.self::RENAMED_OLD_SLUG.'</p>', $this->ReadBlog(self::RENAMED_OLD_SLUG)->content);
        $this->assertSame('<p>Draft target</p>', $this->ReadBlog(self::RENAMED_NEW_SLUG)->content);
    }

    #[Test]
    public function RenamesEverySlugFoundOnTheLiveSite(): void
    {
        $slug_pairs = [
            'เกมและ-ai-เมื่อคณิตศาสตร์เป็นอาวุธลับในการเอาชนะ-' => 'เกมและ-ai-เมื่อคณิตศาสตร์เป็นอาวุธลับในการเอาชนะ',
            'คณิตศาสตร์ในชีวิตประจำวันมีอะไรบ้าง-' => 'คณิตศาสตร์ในชีวิตประจำวันมีอะไรบ้าง',
            'combination-thinking-skill-' => 'combination-thinking-skill',
            'ฟังก์ชัน-คืออะไร-ทำไมเราต้องเรียนรู้เรื่องนี้-' => 'ฟังก์ชัน-คืออะไร-ทำไมเราต้องเรียนรู้เรื่องนี้',
            'ถอดรหัสหลักสูตร-moe-singapore-maths-ทำไมลูกคุณถึงควรเรียนคณิตศาสตร์แนวสิงคโปร์-' => 'ถอดรหัสหลักสูตร-moe-singapore-maths-ทำไมลูกคุณถึงควรเรียนคณิตศาสตร์แนวสิงคโปร์',
            '-number-sense-คืออะไร-ทำไมเด็กเก่งคณิตต้องเริ่มจากสิ่งนี้ก่อนการคิดคำนวณ-' => 'number-sense-คืออะไร-ทำไมเด็กเก่งคณิตต้องเริ่มจากสิ่งนี้ก่อนการคิดคำนวณ',
            'คณิตศาสตร์สิงคโปร์กับการเลือกสายการเรียน-สำคัญแค่ไหนกับสายวิทย์-' => 'คณิตศาสตร์สิงคโปร์กับการเลือกสายการเรียน-สำคัญแค่ไหนกับสายวิทย์',
        ];
        foreach (array_keys($slug_pairs) as $old_slug) {
            $this->CreatePublishedBlog($old_slug);
        }

        $this->LoadMigration()->up();

        foreach ($slug_pairs as $old_slug => $new_slug) {
            $this->assertNull($this->ReadBlog($old_slug));
            $this->assertNotNull($this->ReadBlog($new_slug));
        }
    }

    #[Test]
    public function CanRunTwiceWithoutChangingTheResult(): void
    {
        $this->CreatePublishedBlog(self::RENAMED_OLD_SLUG);
        $migration = $this->LoadMigration();

        $migration->up();
        $migration->up();

        $this->assertSame(1, DB::table('blogs')->where('slug', self::RENAMED_NEW_SLUG)->count());
        $this->assertSame(1, DB::table('blogs')->count());
    }

    #[Test]
    public function RollbackRestoresSlugsAndRepublishesOnlyTheCollidingArticle(): void
    {
        $this->CreatePublishedBlog(self::RENAMED_OLD_SLUG);
        $this->CreatePublishedBlog(self::UNPUBLISHED_SLUG);
        DB::table('blogs')->insert([
            'title' => 'Draft',
            'slug' => 'draft-post',
            'content' => '<p>Draft</p>',
            'is_published' => false,
        ]);
        $migration = $this->LoadMigration();

        $migration->up();
        $migration->down();

        $this->assertNotNull($this->ReadBlog(self::RENAMED_OLD_SLUG));
        $this->assertSame(1, (int) $this->ReadBlog(self::UNPUBLISHED_SLUG)->is_published);
        $this->assertSame(0, (int) $this->ReadBlog('draft-post')->is_published);
    }
}
