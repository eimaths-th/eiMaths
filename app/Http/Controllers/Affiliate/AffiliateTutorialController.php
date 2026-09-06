<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Services\AffiliateTutorialService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliateTutorialController extends Controller
{
    public function Store(Request $request, AffiliateTutorialService $tutorial): JsonResponse
    {
        return response()->json($tutorial->Save($request->user(), $request->only(['version', 'action', 'step'])));
    }
}
