<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BranchAccessAudit extends Command
{
    protected $signature = 'branch:audit {--json : Output structured counts without personal data}';
    protected $description = 'Read-only audit of branch references and privileged role assignments';

    public function handle(): int
    {
        $findings = [
            'users_without_valid_branch' => DB::table('users')->leftJoin('departments', 'users.department_id', '=', 'departments.id')->whereNull('departments.id')->count(),
            'duplicate_branch_codes' => DB::table('departments')->selectRaw('UPPER(TRIM(code)) AS code')->groupByRaw('UPPER(TRIM(code))')->havingRaw('COUNT(*) > 1')->get()->count(),
            'position_branch_mismatches' => DB::table('users')->join('positions', 'users.position_id', '=', 'positions.id')->whereColumn('users.department_id', '!=', 'positions.department_id')->count(),
            'central_role_outside_hq' => DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->join('users', 'users.id', '=', 'model_has_roles.model_id')
                ->where('model_has_roles.model_type', (new \App\Models\User)->getMorphClass())->whereIn('roles.name', ['SystemAdmin', 'Admin'])
                ->where(fn ($q) => $q->whereNull('users.department_id')->orWhere('users.department_id', '!=', 1))->count(),
            'unassigned_crm_leads' => DB::table('crm_leads')->whereNull('branch_id')->count(),
        ];
        foreach (['contacts', 'orders', 'receipts', 'invoices', 'products', 'histrories', 'stocks'] as $table) {
            $findings[$table.'_without_valid_branch'] = DB::table($table)->leftJoin('departments', $table.'.centre', '=', 'departments.id')->whereNull('departments.id')->count();
        }
        $findings['non_hq_all_centre_assignments'] = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('role_has_permissions', 'model_has_roles.role_id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('model_has_roles.model_type', (new \App\Models\User)->getMorphClass())
            ->where('permissions.name', 'all-centre')->where('users.department_id', '!=', 1)->distinct()->count('users.id');
        $findings['direct_non_hq_all_centre_assignments'] = DB::table('model_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->join('users', 'users.id', '=', 'model_has_permissions.model_id')
            ->where('model_has_permissions.model_type', (new \App\Models\User)->getMorphClass())
            ->where('permissions.name', 'all-centre')->where('users.department_id', '!=', 1)->count();
        $findings['parents_with_unresolved_student_links'] = 0;
        foreach (DB::table('parents')->select('id', 'student_id')->cursor() as $parent) {
            $expected = array_unique(array_filter(explode(',', (string) $parent->student_id)));
            $linked = DB::table('parent_student')->where('parent_id', $parent->id)->count();
            if (count($expected) !== $linked) { $findings['parents_with_unresolved_student_links']++; }
        }
        foreach (['contacts' => 'code', 'orders' => 'order_number', 'receipts' => 'receipt_number', 'products' => 'code'] as $table => $column) {
            $findings[$table.'_duplicate_numbers'] = DB::table($table)->whereNotNull($column)->where($column, '!=', '')
                ->select($column)->groupBy($column)->havingRaw('COUNT(*) > 1')->get()->count();
        }
        $this->line($this->option('json') ? json_encode($findings, JSON_PRETTY_PRINT) : 'Read-only: no accounts, roles, or records were changed.');
        if (!$this->option('json')) {
            $this->table(['Check', 'Count'], collect($findings)->map(fn ($count, $key) => [$key, $count])->values()->all());
        }
        return array_sum($findings) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
