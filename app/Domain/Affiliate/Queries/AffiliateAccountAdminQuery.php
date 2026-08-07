<?php

namespace App\Domain\Affiliate\Queries;

use App\Domain\Affiliate\Models\AffiliateProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AffiliateAccountAdminQuery
{
    public function Paginate(int $per_page = 50): LengthAwarePaginator
    {
        return AffiliateProfile::query()->with('User')->latest('id')->paginate(min(max($per_page, 1), 100));
    }
}
