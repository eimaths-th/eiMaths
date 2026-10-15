<?php

namespace App\Domain\Ebook;

use Dompdf\Dompdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

final class DemoData
{
    public static function schema(): void
    {
        if (!app()->environment(['local', 'testing']) || (!config('ebook.demo') && !app()->environment('testing'))) { throw new \RuntimeException('Demo schema requires an isolated local/test database.'); }
        Schema::create('contacts', function (Blueprint $t) {
            $t->id(); $t->string('code'); $t->string('name'); $t->string('nickname')->nullable();
            $t->string('password'); $t->string('type')->default('student'); $t->integer('centre')->default(1);
            $t->integer('discontinued')->default(0); $t->integer('bookuse')->nullable(); $t->integer('bookuse2')->nullable();
            $t->integer('level')->default(1); $t->integer('term')->default(1);
            $t->integer('level2')->nullable(); $t->integer('term2')->nullable(); $t->integer('free_course')->default(0);
        });
        Schema::create('levels', function (Blueprint $t) { $t->id(); $t->string('name'); });
        Schema::create('terms', function (Blueprint $t) { $t->id(); $t->string('name'); $t->integer('level_id'); });
        Schema::create('bookuses', function (Blueprint $t) { $t->id(); $t->string('name'); $t->integer('level_id'); $t->integer('term_id'); $t->integer('type')->default(1); $t->integer('status')->default(1); $t->integer('qty')->default(10); });
        Schema::create('histrories', function (Blueprint $t) {
            $t->id(); $t->integer('student_id'); $t->integer('level_id'); $t->string('term');
            $t->string('bookuse')->nullable(); $t->date('date'); $t->date('start_date')->nullable(); $t->date('end_date')->nullable();
        });
        Schema::create('orders', function (Blueprint $t) { $t->id(); $t->integer('cid'); });
        Schema::create('order_details', function (Blueprint $t) { $t->id(); $t->integer('order_id'); $t->integer('product_id'); });
        Schema::create('order_detail_lists', function (Blueprint $t) { $t->id(); $t->integer('order_id'); $t->string('book'); $t->string('level'); $t->string('term'); });
        (require database_path('migrations/2026_10_08_000001_create_ebook_tables.php'))->up();
        (require database_path('migrations/2026_10_09_000001_improve_ebook_import_and_activity.php'))->up();
    }

    public static function seed(): void
    {
        DB::table('levels')->insert(['id' => 1, 'name' => 'Level 1']);
        DB::table('terms')->insert(['id' => 1, 'name' => 'Term 1', 'level_id' => 1]);
        DB::table('bookuses')->insert([
            ['id' => 1, 'name' => 'G11-G20', 'level_id' => 1, 'term_id' => 1, 'type' => 1],
            ['id' => 2, 'name' => 'G11-G15', 'level_id' => 1, 'term_id' => 1, 'type' => 2],
            ['id' => 3, 'name' => 'G16-G20', 'level_id' => 1, 'term_id' => 1, 'type' => 2],
        ]);
        foreach ([1 => 1, 2 => 3] as $id => $set) {
            DB::table('contacts')->insert(['id' => $id, 'code' => sprintf('DEMO%03d', $id), 'name' => 'นักเรียนตัวอย่าง '.$id,
                'nickname' => $id === 1 ? 'น้องคิด' : 'น้องเก่ง', 'password' => Hash::make('Demo@eiMaths26'), 'bookuse' => $set]);
            DB::table('orders')->insert(['id' => $id, 'cid' => $id]);
            DB::table('order_details')->insert(['order_id' => $id, 'product_id' => $set]);
            DB::table('order_detail_lists')->insert(['order_id' => $id, 'book' => $set === 1 ? 'G11-G20' : 'G16-G20', 'level' => 'Level 1', 'term' => 'Term 1']);
        }
        foreach ([1 => ['G11', 'จำนวนและการนับ', '#F77A26', [1, 2]], 2 => ['G16', 'รูปร่างและแบบรูป', '#248E89', [1, 3]]] as $id => [$code, $title, $accent, $sets]) {
            DB::table('ebook_books')->insert(['id' => $id, 'code' => $code, 'title' => $title, 'description' => 'Level 1 · Term 1', 'accent' => $accent, 'created_at' => now(), 'updated_at' => now()]);
            foreach ($sets as $set) { DB::table('ebook_catalog')->insert(['book_id' => $id, 'bookuse_id' => $set]); }
            DB::table('ebook_versions')->insert(['id' => $id, 'book_id' => $id, 'number' => 1, 'source_path' => 'imports/'.$code.'-demo.pdf', 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public static function createPdf(int $bookId): void
    {
        $book = DB::table('ebook_books')->find($bookId);
        $root = config('ebook.root');
        if (!is_dir($root.'/imports')) { mkdir($root.'/imports', 0700, true); }
        $font = public_path('vendor/spondonit/fonts/poppins/Poppins-Regular.ttf');
        $bold = public_path('vendor/spondonit/fonts/poppins/Poppins-SemiBold.ttf');
        $html = '<html><head><style>@page {margin:0;size:A4} body{margin:0} img{width:210mm;height:297mm;display:block}.page{page-break-after:always}.page:last-child{page-break-after:avoid}</style></head><body>';
        $titles = $bookId === 1 ? ['Count & discover', 'Cut, paste & count', 'My number story'] : ['Meet the shapes', 'Build a pattern', 'Create your picture'];
        foreach ($titles as $index => $title) {
            $im = imagecreatetruecolor(1000, 1414);
            $white = imagecolorallocate($im, 255, 255, 255); $navy = imagecolorallocate($im, 26, 52, 66);
            $orange = imagecolorallocate($im, 247, 122, 38); $teal = imagecolorallocate($im, 36, 142, 137);
            $gray = imagecolorallocate($im, 114, 132, 144); $line = imagecolorallocate($im, 222, 229, 229);
            $pale = imagecolorallocate($im, 247, 248, 242); $pink = imagecolorallocate($im, 243, 180, 173);
            imagefill($im, 0, 0, $white);
            imagefilledrectangle($im, 0, 0, 1000, 20, $bookId === 1 ? $orange : $teal);
            imagettftext($im, 25, 0, 60, 87, $orange, $bold, 'eiMaths');
            imagettftext($im, 14, 0, 730, 82, $gray, $font, 'LEARN BY DOING');
            imagettftext($im, 16, 0, 60, 145, $teal, $bold, $book->code.'   /   LEVEL 1   /   '.sprintf('%02d', $index + 1));
            imagettftext($im, 35, 0, 60, 214, $navy, $bold, $title);
            imagettftext($im, 17, 0, 60, 257, $gray, $font, $index === 1 ? 'Crop a shape below. Paste it in the box and make a group.' : 'Explore the page. Draw, write and share your ideas.');
            imagefilledrectangle($im, 60, 302, 940, 595, $pale);
            for ($n = 0; $n < 5; $n++) {
                $x = 155 + 168 * $n;
                if ($bookId === 1) {
                    imagefilledellipse($im, $x, 420, 90, 90, $n % 2 ? $teal : $orange);
                    imagefilledellipse($im, $x + 10, 366, 38, 17, $teal);
                } elseif ($n % 2) { imagefilledrectangle($im, $x - 42, 380, $x + 42, 465, $teal); }
                else { imagefilledellipse($im, $x, 420, 90, 90, $pink); }
                imagettftext($im, 23, 0, $x - 8, 536, $navy, $bold, (string) ($n + 1));
            }
            imagettftext($im, 22, 0, 60, 676, $navy, $bold, $index === 1 ? 'My cut-and-paste workspace' : 'Let\'s try it together');
            imagesetthickness($im, 2);
            imagerectangle($im, 60, 714, 940, 1167, $line);
            if ($index !== 1) {
                imagettftext($im, 28, 0, 105, 815, $navy, $font, $bookId === 1 ? 'How many can you count?' : 'Draw the next shape.');
                for ($n = 0; $n < 3; $n++) { imagerectangle($im, 130 + $n * 260, 890, 310 + $n * 260, 1080, $line); }
            }
            imagettftext($im, 16, 0, 60, 1238, $gray, $font, 'I discovered:');
            imageline($im, 225, 1245, 940, 1245, $line);
            imagettftext($im, 12, 0, 60, 1355, $gray, $font, 'DEMO WORKSHEET  -  Sample content for testing the ebook app');
            imagettftext($im, 17, 0, 906, 1355, $navy, $bold, (string) ($index + 1));
            ob_start(); imagepng($im); $png = ob_get_clean(); imagedestroy($im);
            $html .= '<div class="page"><img src="data:image/png;base64,'.base64_encode($png).'"></div>';
        }
        $pdf = new Dompdf(['isRemoteEnabled' => false]);
        $pdf->loadHtml($html.'</body></html>'); $pdf->setPaper('A4'); $pdf->render();
        file_put_contents($root.'/imports/'.$book->code.'-demo.pdf', $pdf->output());
    }
}
