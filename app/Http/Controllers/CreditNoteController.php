<?php

namespace App\Http\Controllers;

use App\Domain\CreditNote\Commands\CancelCreditNote;
use App\Domain\CreditNote\Commands\IssueCreditNote;
use App\Domain\CreditNote\Commands\UpdateCreditNoteDraft;
use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Queries\CreditNoteQuery;
use App\Domain\CreditNote\Services\CreditNoteCommandService;
use App\Http\Requests\CreditNote\CancelCreditNoteRequest;
use App\Http\Requests\CreditNote\CreditNoteIndexRequest;
use App\Http\Requests\CreditNote\IssueCreditNoteRequest;
use App\Http\Requests\CreditNote\UpdateCreditNoteRequest;
use App\Models\Receipt;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CreditNoteController extends Controller
{
    public function __construct(
        private readonly CreditNoteQuery $query,
        private readonly CreditNoteCommandService $command_service,
    ) {
    }

    public function Index(CreditNoteIndexRequest $request): View|JsonResponse
    {
        $paginator = $this->query->ForList($request->user(), $request->validated())->paginate((int) ($request->validated('per_page') ?? 25));
        if ($request->expectsJson()) {
            return response()->json(['data' => $paginator, 'correlation_id' => $this->CorrelationId($request)]);
        }
        return view('credit-notes.index', ['credit_notes' => $paginator]);
    }

    public function Store(Request $request, Receipt $receipt): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $receipt = $this->query->FindReceiptVisibleOrFail($request->user(), $receipt->id);
        $this->authorize('create', $receipt);
        $note = $this->command_service->CreateDraft($receipt, $request->user(), $this->CorrelationId($request));
        if ($request->expectsJson()) {
            return response()->json(['data' => $this->Serialize($note, $this->CorrelationId($request)), 'correlation_id' => $this->CorrelationId($request)], 201);
        }
        return redirect()->route('credit-notes.show', $note)->with('success', __('credit-note.flash.created'));
    }

    public function Show(Request $request, int $credit_note): View|JsonResponse
    {
        $note = $this->query->Document($request->user(), $credit_note);
        $this->authorize('show', $note);
        if ($request->expectsJson()) {
            return response()->json(['data' => $this->Serialize($note), 'correlation_id' => $this->CorrelationId($request)]);
        }
        return view('credit-notes.show', [
            'credit_note' => $note,
            'source_snapshot' => $note->status?->value === 'draft' ? $this->query->EditorSource($note) : null,
        ]);
    }

    public function Update(UpdateCreditNoteRequest $request, int $credit_note): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $note = $this->query->FindVisibleOrFail($request->user(), $credit_note);
        $this->authorize('edit', $note);
        $validated = $request->validated();
        $credit_items = [];
        foreach ($validated['items'] as $item) {
            $credit_amount = trim((string) ($item['credit_amount'] ?? ''));
            if ($credit_amount === '' || \App\Domain\CreditNote\ValueObjects\CreditNoteMoney::FromDecimal($credit_amount, true)->Minor() === 0) {
                continue;
            }
            $credit_items[] = [
                'source_type' => $item['source_type'],
                'source_id' => (int) $item['source_id'],
                'credit_quantity' => $item['credit_quantity'] ?? null,
                'credit_amount_minor' => \App\Domain\CreditNote\ValueObjects\CreditNoteMoney::FromDecimal($credit_amount, true)->Minor(),
            ];
        }
        $updated = $this->command_service->UpdateDraft($note, new UpdateCreditNoteDraft(
            (int) $validated['lock_version'],
            array_intersect_key($validated, array_flip(['buyer_name', 'buyer_address', 'buyer_tax_id', 'buyer_is_vat_registered', 'buyer_branch_type', 'buyer_branch_code'])),
            $validated['reason_code'],
            (string) ($validated['reason_details'] ?? ''),
            CarbonImmutable::createFromFormat('Y-m-d', $validated['adjustment_event_date'])->startOfDay(),
            $validated['late_issue_reason'] ?? null,
            $credit_items,
        ), $request->user(), $this->CorrelationId($request));
        if ($request->expectsJson()) {
            return response()->json(['data' => $this->Serialize($updated, $this->CorrelationId($request)), 'correlation_id' => $this->CorrelationId($request)]);
        }
        return redirect()->route('credit-notes.show', $updated)->with('success', __('credit-note.flash.saved'));
    }

    public function Issue(IssueCreditNoteRequest $request, int $credit_note): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $note = $this->query->FindVisibleOrFail($request->user(), $credit_note);
        $this->authorize('issue', $note);
        $issued = $this->command_service->Issue($note, new IssueCreditNote((int) $request->validated('lock_version'), (string) $request->validated('idempotency_key')), $request->user(), $this->CorrelationId($request));
        if ($request->expectsJson()) {
            return response()->json(['data' => $this->Serialize($issued, $this->CorrelationId($request)), 'correlation_id' => $this->CorrelationId($request)]);
        }
        return redirect()->route('credit-notes.show', $issued)->with('success', __('credit-note.flash.issued'));
    }

    public function Cancel(CancelCreditNoteRequest $request, int $credit_note): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $note = $this->query->FindVisibleOrFail($request->user(), $credit_note);
        $this->authorize('cancel', $note);
        $cancelled = $this->command_service->Cancel($note, new CancelCreditNote((string) $request->validated('reason'), (string) $request->validated('idempotency_key')), $request->user(), $this->CorrelationId($request));
        if ($request->expectsJson()) {
            return response()->json(['data' => $this->Serialize($cancelled, $this->CorrelationId($request)), 'correlation_id' => $this->CorrelationId($request)]);
        }
        return redirect()->route('credit-notes.show', $cancelled)->with('success', __('credit-note.flash.cancelled'));
    }

    private function Serialize(CreditNote $note, ?string $correlation_id = null): array
    {
        return [
            'id' => $note->id,
            'number' => $note->number,
            'receipt_id' => $note->receipt_id,
            'centre_id' => $note->centre_id,
            'status' => $note->status?->value,
            'lock_version' => $note->lock_version,
            'issue_date' => $note->issue_date?->toDateString(),
            'cancellation_effective_date' => $note->cancellation_effective_date?->toDateString(),
            'difference_grand_total_minor' => $note->difference_grand_total_minor,
            'correlation_id' => $correlation_id,
        ];
    }

    private function CorrelationId(Request $request): string
    {
        return (string) ($request->attributes->get('credit_note_correlation_id') ?: \Illuminate\Support\Str::uuid());
    }
}
