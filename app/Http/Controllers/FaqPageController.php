<?php

namespace App\Http\Controllers;

use App\Services\Faq\FaqPageResolver;
use App\Services\Faq\UnavailableFaqUrls;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class FaqPageController extends Controller
{
    public function __construct(private readonly FaqPageResolver $resolver)
    {
    }

    public function ById(Request $request, string $faq_id): JsonResponse
    {
        return $this->Respond(function () use ($request, $faq_id): array {
            abort_unless(preg_match('/^[1-9][0-9]*$/D', $faq_id)
                && (string) (int) $faq_id === $faq_id, 404);
            return $this->resolver->ById((int) $faq_id, $this->Locale($request));
        });
    }

    public function Resolve(Request $request, string $slug): JsonResponse
    {
        return $this->Respond(fn (): array => $this->resolver->Resolve($slug, $this->Locale($request)));
    }

    private function Locale(Request $request): string
    {
        return in_array($request->query('locale'), ['th', 'en'], true) ? $request->query('locale') : 'th';
    }

    private function Respond(\Closure $read): JsonResponse
    {
        if (!config('faq-urls.enabled', false)) throw new UnavailableFaqUrls('faq.urls.disabled');
        try {
            return response()->json($read())->header('Cache-Control', 'no-store');
        } catch (HttpExceptionInterface $exception) {
            throw $exception;
        } catch (UnavailableFaqUrls $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new UnavailableFaqUrls('faq.urls.resolve');
        }
    }
}
