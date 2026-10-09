<?php

namespace App\Domain\Ebook;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Process;

final class PdfConverter
{
    public function convert(int $versionId): void
    {
        $version = DB::table('ebook_versions')->find($versionId);
        if (!$version || $version->status === 'ready' || $version->published_at) { return; }
        $root = config('ebook.root');
        $source = $root.DIRECTORY_SEPARATOR.$version->source_path;
        DB::table('ebook_versions')->where('id', $versionId)->update(['status' => 'processing', 'error' => null, 'updated_at' => now()]);
        try {
            $info = new Process([config('ebook.pdfinfo'), $source]);
            $info->setTimeout(30)->mustRun();
            if (!preg_match('/^Pages:\s+(\d+)/m', $info->getOutput(), $match)) { throw new RuntimeException('อ่านจำนวนหน้า PDF ไม่สำเร็จ'); }
            $count = (int) $match[1];
            if ($count < 1 || $count > config('ebook.max_pages')) { throw new RuntimeException('รองรับ PDF ไม่เกิน '.config('ebook.max_pages').' หน้า'); }
            if (preg_match('/^Encrypted:\s+yes/im', $info->getOutput())) { throw new RuntimeException('กรุณาใช้ PDF ที่ไม่ติดรหัสผ่าน'); }
            $relative = 'versions/'.$versionId.'/pages';
            $directory = $root.DIRECTORY_SEPARATOR.$relative;
            if (!is_dir($directory)) { mkdir($directory, 0700, true); }
            $pages = [];
            for ($number = 1; $number <= $count; $number++) {
                $name = sprintf('page-%04d', $number);
                $render = new Process([config('ebook.pdftoppm'), '-f', (string) $number, '-l', (string) $number,
                    '-singlefile', '-scale-to', '1600', '-png', $source, $directory.DIRECTORY_SEPARATOR.$name]);
                $render->setTimeout(90)->mustRun();
                $size = getimagesize($directory.DIRECTORY_SEPARATOR.$name.'.png');
                if (!$size) { throw new RuntimeException('แปลงภาพหน้า '.$number.' ไม่สำเร็จ'); }
                $pages[] = ['version_id' => $versionId, 'number' => $number, 'image_path' => $relative.'/'.$name.'.png', 'width' => $size[0], 'height' => $size[1]];
            }
            DB::transaction(function () use ($pages, $versionId, $count) {
                DB::table('ebook_pages')->where('version_id', $versionId)->delete();
                DB::table('ebook_pages')->insert($pages);
                DB::table('ebook_versions')->where('id', $versionId)->update(['status' => 'ready', 'page_count' => $count, 'updated_at' => now()]);
            });
        } catch (\Throwable $e) {
            DB::table('ebook_versions')->where('id', $versionId)->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 1000), 'updated_at' => now()]);
            throw $e;
        }
    }
}
