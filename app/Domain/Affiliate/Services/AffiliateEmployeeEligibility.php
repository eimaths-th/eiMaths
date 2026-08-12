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

        return $user->getRoleNames()
            ->intersect($employee_roles)
            ->isNotEmpty();
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
