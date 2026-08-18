<?php

namespace App\Domain\Affiliate\Services;

use App\Domain\Affiliate\Exceptions\AffiliateDomainException;
use App\Domain\Affiliate\Models\AffiliateConfiguration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AffiliateConfigurationService
{
    private array $resolved_values = [];

    public function __construct(
        private readonly AffiliateAuditService $audit_service,
    ) {
    }

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

    public function CurrentSettings(): array
    {
        return [
            'commission_rate_basis_points' => $this->CommissionRateBasisPoints(),
            'commission_holding_days' => $this->CommissionHoldingDays(),
            'attribution_duration_days' => $this->AttributionDurationDays(),
            'minimum_withdrawal_minor' => $this->MinimumWithdrawalMinor(),
            'withdrawal_approval_required' => $this->IsWithdrawalApprovalRequired(),
        ];
    }

    public function IsWithdrawalApprovalRequired(): bool
    {
        $value = $this->Setting('withdrawal_approval_required');
        $normalized = strtolower(trim((string) $value));
        if (!in_array($normalized, ['1', 'true'], true)) {
            throw AffiliateDomainException::ConfigurationInvalid('Withdrawal approval must remain mandatory.');
        }

        return true;
    }

    public function UpdateApprovedSettings(array $validated_settings, User $actor, string $correlation_id): void
    {
        $this->AssertAffiliateWritesAvailable();
        DB::transaction(function () use ($validated_settings, $actor, $correlation_id): void {
            $prior_state = [];
            $resulting_state = [];

            foreach ($validated_settings as $key => $value) {
                $this->ValidateSettingValue($key, $value);
                $configuration = $this->ConfigurationRow($key);
                $normalized_value = $this->NormalizedSettingValue($key, $value);
                $prior_state[$key] = (string) $configuration->value;
                $updated = AffiliateConfiguration::query()->where('key', $key)->update([
                    'value' => $normalized_value,
                    'updated_at' => now(),
                ]);
                if ($updated !== 1) {
                    throw AffiliateDomainException::ConfigurationInvalid('Configuration setting must exist exactly once.');
                }
                $resulting_state[$key] = $normalized_value;
            }

            $this->audit_service->Record(
                'configuration.update',
                'affiliate_configurations',
                null,
                $actor,
                $prior_state,
                $resulting_state,
                null,
                'SUCCESS',
                null,
                $correlation_id,
            );
        });

        $this->resolved_values = [];
    }

    public function AssertAffiliateWritesAvailable(): void
    {
        if ((bool) config('affiliate.maintenance_mode', false)) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate maintenance mode is active.');
        }
    }

    public function AssertAffiliateRegistrationDeliveryAvailable(): void
    {
        $this->AssertAffiliateWritesAvailable();

        if (
            strtolower((string) config('app.env')) === 'production'
            && strtolower(trim((string) config('mail.default'))) === 'log'
        ) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate registration email delivery is not configured.');
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

        return $this->resolved_values[$key] = $this->ConfigurationRow($key)->value;
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
            if (!in_array($value, [true, 1, '1', 'true'], true)) {
                throw AffiliateDomainException::ConfigurationInvalid('Withdrawal approval must remain mandatory.');
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
        if ($key === 'attribution_duration_days' && (int) $value < 1) {
            throw AffiliateDomainException::ConfigurationInvalid('Attribution duration must be positive.');
        }
        if ($key === 'minimum_withdrawal_minor' && (int) $value < 1) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate configuration must be positive.');
        }
    }

    private function ConfigurationRow(string $key): AffiliateConfiguration
    {
        $rows = AffiliateConfiguration::query()->where('key', $key)->get();
        if ($rows->count() !== 1 || $rows->first()->value === null) {
            throw AffiliateDomainException::ConfigurationInvalid('Affiliate configuration setting is missing or duplicated.');
        }

        return $rows->first();
    }

    private function NormalizedSettingValue(string $key, mixed $value): string
    {
        return $key === 'withdrawal_approval_required' ? '1' : (string) $value;
    }
}
