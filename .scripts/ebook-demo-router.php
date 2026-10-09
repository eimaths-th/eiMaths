<?php
// Bind PHP's built-in server to 127.0.0.1 only. Android reaches it through adb reverse.
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (is_string($requestPath) && preg_match('#^/(images|dist|plugins|vendor|css|js)/#', $requestPath)) {
    $public = realpath(__DIR__.'/../public');
    $asset = realpath($public.$requestPath);
    if ($asset && str_starts_with($asset, $public.DIRECTORY_SEPARATOR) && is_file($asset)) { return false; }
}
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
App\Domain\Ebook\DemoEnvironment::configure();
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
$response->send();
$kernel->terminate($request, $response);
