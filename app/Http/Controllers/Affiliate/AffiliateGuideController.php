<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AffiliateGuideController extends Controller
{
    public function Index(): View
    {
        return view('affiliate.guide.index');
    }

    public function Document(): BinaryFileResponse
    {
        $document_path = resource_path('documents/affiliate/understanding-summary.pdf');

        abort_unless(is_file($document_path), 404);

        return response()->file($document_path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="understanding-summary.pdf"',
        ]);
    }
}
