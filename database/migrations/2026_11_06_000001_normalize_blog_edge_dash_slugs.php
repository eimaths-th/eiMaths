<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SLUGS_TO_NORMALIZE = [
        'เกมและ-ai-เมื่อคณิตศาสตร์เป็นอาวุธลับในการเอาชนะ-',
        'คณิตศาสตร์ในชีวิตประจำวันมีอะไรบ้าง-',
        'combination-thinking-skill-',
        'ฟังก์ชัน-คืออะไร-ทำไมเราต้องเรียนรู้เรื่องนี้-',
        'ถอดรหัสหลักสูตร-moe-singapore-maths-ทำไมลูกคุณถึงควรเรียนคณิตศาสตร์แนวสิงคโปร์-',
        '-number-sense-คืออะไร-ทำไมเด็กเก่งคณิตต้องเริ่มจากสิ่งนี้ก่อนการคิดคำนวณ-',
        'คณิตศาสตร์สิงคโปร์กับการเลือกสายการเรียน-สำคัญแค่ไหนกับสายวิทย์-',
    ];

    private const SLUG_TO_UNPUBLISH = 'ทำไมต้องเรียนคณิตศาสตร์-';

    public function up(): void
    {
        DB::transaction(function (): void {
            foreach (self::SLUGS_TO_NORMALIZE as $old_slug) {
                $this->RenameUnlessTargetExists($old_slug, trim($old_slug, '-'));
            }

            DB::table('blogs')->where('slug', self::SLUG_TO_UNPUBLISH)->update(['is_published' => false]);
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            foreach (self::SLUGS_TO_NORMALIZE as $old_slug) {
                $this->RenameUnlessTargetExists(trim($old_slug, '-'), $old_slug);
            }

            DB::table('blogs')->where('slug', self::SLUG_TO_UNPUBLISH)->update(['is_published' => true]);
        });
    }

    private function RenameUnlessTargetExists(string $source_slug, string $target_slug): void
    {
        if (DB::table('blogs')->where('slug', $target_slug)->exists()) {
            return;
        }

        DB::table('blogs')->where('slug', $source_slug)->update(['slug' => $target_slug]);
    }
};
