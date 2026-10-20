<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

if (PHP_SAPI !== 'cli' || is_file(dirname(__DIR__, 2).'/bootstrap/cache/config.php')) exit(1);
putenv('APP_ENV=testing');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('SESSION_DRIVER=array');
putenv('CACHE_DRIVER=array');
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$worker = new class {
    use \Tests\Support\FaqSlugDatabaseProfile;

    public function Run(): void
    {
        $this->ConfigureSlugDatabase();
        $barrier = getenv('FAQ_SLUG_WORKER_BARRIER');
        $ready = getenv('FAQ_SLUG_WORKER_READY');
        $number = getenv('FAQ_SLUG_WORKER_NUMBER');
        $round = getenv('FAQ_SLUG_WORKER_ROUND');
        $faq_id = getenv('FAQ_SLUG_WORKER_FAQ_ID');
        if (!is_string($barrier) || !is_string($ready)
            || dirname($barrier) !== dirname($ready)
            || realpath(sys_get_temp_dir()) !== realpath(dirname(dirname($barrier)))
            || !preg_match('/^faq-slug-barrier-[a-f0-9]{32}$/D', basename(dirname($barrier)))
            || basename($barrier) !== 'go' || !preg_match('/^ready-[0-7]$/D', basename($ready))
            || !is_string($number) || !preg_match('/^[0-7]$/D', $number)
            || !is_string($round) || !preg_match('/^[1-3]$/D', $round)
            || !is_string($faq_id) || !preg_match('/^[1-9][0-9]*$/D', $faq_id)) {
            throw new \RuntimeException('Invalid worker boundary.');
        }
        $faq = \App\Models\Faq::query()->findOrFail((int) $faq_id);
        file_put_contents($ready, 'ready');
        $deadline = microtime(true) + 20;
        while (!is_file($barrier)) {
            clearstatcache();
            if (microtime(true) >= $deadline) throw new \RuntimeException('Worker barrier timed out.');
            usleep(10000);
        }
        // Invoke the existing application write boundary; authorization is covered by feature tests.
        $request = Request::create('/faqs/'.$faq->id, 'PUT', [
            'category_id' => $faq->category_id, 'question' => 'Worker '.$round.' '.$number,
            'question_en' => 'English question', 'answer' => 'Answer',
            'position' => 0, 'is_published' => true,
        ]);
        app(\App\Http\Controllers\FaqController::class)->Update($request, $faq);
    }
};
$exit_status = 0;
try {
    $worker->Run();
} catch (\Throwable $exception) {
    fwrite(STDERR, 'FAQ slug worker failed ('.get_class($exception).').'.PHP_EOL);
    $exit_status = 1;
} finally {
    if (config('database.connections.faq_slug_test') !== null) DB::disconnect('faq_slug_test');
}
exit($exit_status);
