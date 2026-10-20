<?php
namespace App\Http\Controllers;
use App\Services\Faq\FaqReadRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
final class FaqReadController extends Controller
{
    public function Store(Request $request, FaqReadRecorder $recorder)
    {
        try {
            $root = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $root = null;
        }
        $payload = $root instanceof \stdClass ? (array) $root : [];
        if (!$request->isJson() || !($root instanceof \stdClass) || array_diff(array_keys($payload), ['faq_id', 'event_key']) !== []
            || !is_int($payload['faq_id'] ?? null) || $payload['faq_id'] <= 0
            || !is_string($payload['event_key'] ?? null)
            || preg_match('/^[a-f0-9]{64}$/D', $payload['event_key']) !== 1) {
            return response()->json(['error_code' => 'FAQ_READ_INVALID', 'message' => 'Invalid recording request.'], 422);
        }
        $request_id = $request->header('X-Request-Id');
        $correlation_id = is_string($request_id) && preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/iD', $request_id)
            ? strtolower($request_id) : (string) Str::uuid();
        try {
            $recorder->Record($payload['faq_id'], $payload['event_key']);
        } catch (Throwable $exception) {
            if ($exception instanceof HttpExceptionInterface && in_array($exception->getStatusCode(), [404, 409], true)) {
                return response()->json(['error_code' => $exception->getStatusCode() === 404 ? 'FAQ_NOT_FOUND' : 'FAQ_READ_CONFLICT',
                    'message' => 'This recording request cannot be accepted.'], $exception->getStatusCode());
            }
            Log::warning('faq.read.failed', ['operation' => 'faq.read.record', 'faq_id' => $payload['faq_id'],
                'outcome' => 'failed', 'error_code' => 'FAQ_READ_UNAVAILABLE', 'correlation_id' => $correlation_id]);
            return response()->json(['error_code' => 'FAQ_READ_UNAVAILABLE', 'message' => 'Read recording is unavailable.'], 503);
        }
        return response()->noContent();
    }
}
