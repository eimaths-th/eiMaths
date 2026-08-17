<?php

namespace App\Http\Controllers;

use App\Domain\CreditNote\Models\CreditNote;
use App\Domain\CreditNote\Queries\CreditNoteQuery;
use App\Exports\CreditNoteRegisterExport;
use App\Http\Requests\CreditNote\CreditNoteExportRequest;
use Maatwebsite\Excel\Facades\Excel;

final class CreditNoteExportController extends Controller
{
    public function __construct(private readonly CreditNoteQuery $query)
    {
    }

    public function Export(CreditNoteExportRequest $request)
    {
        $this->authorize('export', CreditNote::class);
        return Excel::download(
            new CreditNoteRegisterExport($request->user(), $request->validated(), $this->query),
            'credit-notes.xlsx',
        );
    }
}
