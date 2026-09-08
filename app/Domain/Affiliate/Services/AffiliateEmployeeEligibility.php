<?php

namespace App\Domain\Affiliate\Services;

use App\Models\User;

final class AffiliateEmployeeEligibility
{
    public function IsEligible(User $user): bool
    {
        $employee_roles = $this->EmployeeRoles();
        if ($employee_roles === []) {
            return false;
        }

        $has_employee_role = $user->getRoleNames()
            ->intersect($employee_roles)
            ->isNotEmpty();
        if (!$has_employee_role) {
            return false;
        }

        // SystemAdmin is the platform owner and intentionally bypasses role permissions.
        if ($user->isSystemAdmin()) {
            return true;
        }

        // The allowlist identifies staff accounts; affiliate-user is the
        // role-managed switch that enables their self-service Affiliate access.
        if (!\Spatie\Permission\Models\Permission::query()
            ->where('name', 'affiliate-user')
            ->where('guard_name', 'web')
            ->exists()) {
            return false;
        }

        return $user->can('affiliate-user');
    }

    public function IsAffiliateOnly(User $user): bool
    {
        return $user->hasRole('Affiliate-user') && !$this->IsEligible($user);
    }

    private function EmployeeRoles(): array
    {
        $configured_roles = config('affiliate.employee_roles', []);
        if (!is_array($configured_roles)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $role_name): string => is_string($role_name) ? trim($role_name) : '',
            $configured_roles,
        ))));
    }
}
