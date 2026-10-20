<?php

namespace App\Services\Faq;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class UnavailableFaqUrls extends RuntimeException
{
    public function __construct(public readonly string $operation = 'faq.urls.unavailable')
    {
        parent::__construct('FAQ URL metadata is temporarily unavailable.');
    }

    public function report(): void
    {
        Log::error('faq.urls.unavailable', [
            'operation' => $this->operation,
            'error_code' => 'FAQ_CONTENT_UNAVAILABLE',
        ]);
    }

    public function render(Request $request): Response
    {
        if ($request->is('api/*')) {
            return response()->json(['error_code' => 'FAQ_CONTENT_UNAVAILABLE',
                'message' => 'FAQ information is temporarily unavailable.'], 503)
                ->header('Cache-Control', 'no-store');
        }
        return response(__('content-management.faq.messages.save_unavailable'), 503)
            ->header('Cache-Control', 'no-store');
    }
}
