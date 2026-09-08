<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PublicBranchController extends Controller
{
    public function index()
    {
        $branches = DB::table('departments')->where('status', 1)->where('branch_type', 'branch')
            ->orderBy('name')->get(['id', 'code', 'name', 'enabled_modules'])
            ->filter(fn ($branch) => $branch->enabled_modules !== null && in_array('crm', json_decode($branch->enabled_modules, true) ?? [], true))
            ->map(fn ($branch) => ['id' => $branch->id, 'code' => $branch->code, 'name' => $branch->name])->values();
        return response()->json(['data' => $branches]);
    }
}
