<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateConfiguration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AffiliateConfigurationService
{
    private array $resolved_values = [];

    public function CommissionRateBasisPoints(): int
    {
        return $this->IntegerSetting('commission_rate_basis_points', 0, 10000);
    }

    public function CommissionHoldingDays(): int
    {
        return $this->IntegerSetting('commission_holding_days', 0, null);
    }

    public function AttributionDurationDays(): int
    {
        return $this->IntegerSetting('attribution_duration_days', 1, null);
    }

    public function MinimumWithdrawalMinor(): int
    {
        return $this->IntegerSetting('minimum_withdrawal_minor', 1, null);
    }

    public function IsWithdrawalApprovalRequired(): bool
    {
        $value = $this->Setting('withdrawal_approval_required');
        $normalized = strtolower(trim((string) $value));
        if (!in_array($normalized, ['0', '1', 'true', 'false'], true)) {
            throw AffiliateDomainException::ConfigurationInvalid('Withdrawal approval configuration must be boolean.');
        }

        return in_array($normalized, ['1', 'true'], true);
    }

    public function UpdateApprovedSettings(array $validated_settings, User $actor, string $correlation_id): void
    {
        $this->AssertAffiliateWritesAvailable();
        DB::transaction(function () use ($validated_settings): void {
            foreach ($validated_settings as $key => $value) {
                $this->ValidateSettingValue($key, $value);
                $updated = AffiliateConfiguration::query()->where('key', $key)->update([
                    'value' => (string) $value,
                    'updated_at' => now(),
                ]);
                if ($updated !== 1) {
                    throw AffiliateDomainException::ConfigurationInvalid('Configuration setting must exist exactly once.');
                }
            }
        });

        $this->resolved_values = [];
    }

    public function AssertAffiliateWritesAvailable(): void
    {
        if ((bool) config('affiliate.maintenance_mode', false)) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate maintenance mode is active.');
        }
    }

    private function IntegerSetting(string $key, int $minimum, ?int $maximum): int
    {
        $value = (int) $this->Setting($key);
        $raw_value = (string) $this->Setting($key);
        if (!preg_match('/\A\d+\z/', trim($raw_value))) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate configuration must be an integer.');
        }
        if ($value < $minimum || ($maximum !== null && $value > $maximum)) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate configuration is outside its approved range.');
        }

        return $value;
    }

    private function Setting(string $key): mixed
    {
        if (array_key_exists($key, $this->resolved_values)) {
            return $this->resolved_values[$key];
        }

        $rows = AffiliateConfiguration::query()->where('key', $key)->get();
        if ($rows->count() !== 1 || $rows->first()->value === null) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate configuration setting is missing or duplicated.');
        }

        return $this->resolved_values[$key] = $rows->first()->value;
    }

    private function ValidateSettingValue(string $key, mixed $value): void
    {
        if (!in_array($key, [
            'commission_rate_basis_points',
            'commission_holding_days',
            'attribution_duration_days',
            'minimum_withdrawal_minor',
            'withdrawal_approval_required',
        ], true)) {
            throw AffiliateDomainException::ConfigurationInvalid('Unknown Affiliate configuration setting.');
        }

        if ($key === 'withdrawal_approval_required') {
            if (!in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
                throw AffiliateDomainException::ConfigurationInvalid('Withdrawal approval configuration must be boolean.');
            }
            return;
        }

        if (!is_int($value) && !is_string($value)) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate configuration must be an integer.');
        }
        $integer_value = (string) $value;
        if (!preg_match('/\A\d+\z/', $integer_value)) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate configuration must be an integer.');
        }
        if ($key === 'commission_rate_basis_points' && (int) $value > 10000) {
            throw AffiliateDomainException::ConfigurationInvalid('Commission rate is outside its approved range.');
        }
        if ($key === 'commission_holding_days' && (int) $value < 0) {
            throw AffiliateDomainException::ConfigurationInvalid('Holding days cannot be negative.');
        }
        if ($key !== 'commission_holding_days' && (int) $value < 1) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate configuration must be positive.');
        }
    }
}
