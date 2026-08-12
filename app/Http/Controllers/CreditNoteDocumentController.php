<?php

namespace App\Http\Controllers;

use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Domain\CreditNote\Queries\CreditNoteQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

final class CreditNoteDocumentController extends Controller
{
    public function __construct(private readonly CreditNoteQuery $query)
    {
    }

    public function PrintDocument(Request $request, int $credit_note): View
    {
        $note = $this->query->Document($request->user(), $credit_note);
        $this->authorize('show', $note);
        return view('credit-notes.print', ['credit_note' => $note]);
    }

    public function PdfDocument(Request $request, int $credit_note)
    {
        $note = $this->query->Document($request->user(), $credit_note);
        $this->authorize('show', $note);
        $filename = $note->number !== null ? $note->number . '.pdf' : 'DRAFT-CN-' . $note->id . '.pdf';
        try {
            return Pdf::loadView('credit-notes.documents.credit-note', ['credit_note' => $note])
                ->stream($filename);
        } catch (Throwable) {
            throw CreditNoteDomainException::PdfGenerationFailed();
        }
    }
}
