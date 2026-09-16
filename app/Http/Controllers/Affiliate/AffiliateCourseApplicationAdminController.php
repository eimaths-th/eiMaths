<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Queries\AffiliateCourseApplicationAdminQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliateCourseApplicationAdminController extends Controller
{
    public function __construct(
        private readonly AffiliateCourseApplicationAdminQuery $application_query,
    ) {}

    public function PendingSummary(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->application_query->PendingSummary($request->user()),
        ]);
    }
}
