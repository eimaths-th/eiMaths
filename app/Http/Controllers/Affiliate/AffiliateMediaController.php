<?php

namespace App\Http\Controllers\Affiliate;

use App\Domain\Affiliate\Queries\AffiliateMediaQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliate\AffiliateMediaFilterRequest;
use Illuminate\View\View;

class AffiliateMediaController extends Controller
{
    public function __construct(private readonly AffiliateMediaQuery $media_query)
    {
    }

    public function Index(AffiliateMediaFilterRequest $request): View
    {
        return view('affiliate.media.index', [
            'media' => $this->media_query->ForUser(
                $request->user(),
                $request->validated('search'),
                $request->validated('type'),
            ),
            'search' => $request->validated('search'),
            'type' => $request->validated('type'),
        ]);
    }
}
