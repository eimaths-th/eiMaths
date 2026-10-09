<?php
// Test-only helper: the isolated demo account may outlive a reinstalled test APK.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
App\Domain\Ebook\DemoEnvironment::configure();
$student = Illuminate\Support\Facades\DB::table('contacts')->where('code', 'DEMO001')->first();
$session = Illuminate\Support\Facades\DB::table('ebook_sessions')->where('student_id', $student->id)->first();
if ($session) {
    App\Domain\Ebook\Audit::record($session, 'qa_session_reset');
    Illuminate\Support\Facades\DB::table('ebook_sessions')->where('id', $session->id)->delete();
}
echo "Released isolated DEMO001 Session for emulator testing. Student work preserved.\n";
