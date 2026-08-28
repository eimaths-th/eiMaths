<?php

namespace App\Domain\Affiliate\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AffiliatePreflightService
{
    private const STATUS_PASS = 'PASS';
    private const STATUS_WARN = 'WARN';
    private const STATUS_BLOCKED = 'BLOCKED';
    private const STATUS_UNKNOWN = 'UNKNOWN';

    public function __construct(
        private readonly AffiliateConfigurationService $configuration_service,
    ) {
    }

    public function Checks(): array
    {
        return [
            $this->MaintenanceModeCheck(),
            $this->ProductionDebugCheck(),
            $this->RegistrationMailDriverCheck(),
            $this->QueueDriverCheck(),
            $this->AffiliateSettingsCheck(),
            $this->VisitorHashKeyCheck(),
            $this->DestinationHostsCheck(),
            $this->AffiliateProfileOwnershipCheck(),
            $this->MailDeliveryCheck(),
            $this->SchedulerHostCheck(),
            $this->AccessReviewCheck(),
            $this->FinancePolicyCheck(),
        ];
    }

    private function MaintenanceModeCheck(): array
    {
        if ((bool) config('affiliate.maintenance_mode', false)) {
            return $this->BuildCheck('maintenance_mode', self::STATUS_BLOCKED, 'Affiliate maintenance mode is active; writes are intentionally blocked.');
        }

        return $this->BuildCheck('maintenance_mode', self::STATUS_PASS, 'Affiliate maintenance mode is inactive.');
    }

    private function ProductionDebugCheck(): array
    {
        $environment = strtolower(trim((string) config('app.env')));
        if ($environment !== 'production') {
            return $this->BuildCheck('production_debug', self::STATUS_UNKNOWN, "Current environment is {$environment}; production debug configuration is not evidenced.");
        }

        if ((bool) config('app.debug', false)) {
            return $this->BuildCheck('production_debug', self::STATUS_BLOCKED, 'Production debug mode is enabled.');
        }

        return $this->BuildCheck('production_debug', self::STATUS_PASS, 'Production debug mode is disabled.');
    }

    private function RegistrationMailDriverCheck(): array
    {
        $environment = strtolower(trim((string) config('app.env')));
        $mail_driver = strtolower(trim((string) config('mail.default')));

        if ($mail_driver === '') {
            return $this->BuildCheck('registration_mail_driver', self::STATUS_BLOCKED, 'The default mail driver is empty.');
        }

        if ($environment === 'production' && $mail_driver === 'log') {
            return $this->BuildCheck('registration_mail_driver', self::STATUS_BLOCKED, 'Production Affiliate registration cannot use the log mail driver.');
        }

        if ($mail_driver === 'log') {
            return $this->BuildCheck('registration_mail_driver', self::STATUS_WARN, 'The current mail driver is log; local registration mail is not externally delivered.');
        }

        return $this->BuildCheck('registration_mail_driver', self::STATUS_PASS, "Mail driver {$mail_driver} is configured; delivery still requires external verification.");
    }

    private function QueueDriverCheck(): array
    {
        $queue_driver = strtolower(trim((string) config('queue.default')));

        if ($queue_driver === '') {
            return $this->BuildCheck('queue_driver', self::STATUS_BLOCKED, 'The default queue driver is empty.');
        }

        if (in_array($queue_driver, ['sync', 'null'], true)) {
            return $this->BuildCheck('queue_driver', self::STATUS_WARN, "Queue driver {$queue_driver} does not provide a durable asynchronous queue.");
        }

        return $this->BuildCheck('queue_driver', self::STATUS_PASS, "Queue driver {$queue_driver} is configured; worker health still requires external verification.");
    }

    private function AffiliateSettingsCheck(): array
    {
        try {
            $this->configuration_service->CommissionRateBasisPoints();
            $this->configuration_service->CommissionHoldingDays();
            $this->configuration_service->AttributionDurationDays();
            $this->configuration_service->MinimumWithdrawalMinor();
            $this->configuration_service->IsWithdrawalApprovalRequired();
        } catch (Throwable) {
            return $this->BuildCheck('affiliate_settings', self::STATUS_BLOCKED, 'Affiliate database settings are missing, duplicated, or invalid.');
        }

        return $this->BuildCheck('affiliate_settings', self::STATUS_PASS, 'Affiliate database settings passed type and range validation.');
    }

    private function VisitorHashKeyCheck(): array
    {
        if (!(bool) config('affiliate.features.click_tracking', false)) {
            return $this->BuildCheck('visitor_hash_key', self::STATUS_UNKNOWN, 'Click tracking is disabled; the independent visitor hash key is not required.');
        }

        $visitor_hash_key = (string) config('affiliate.visitor_hash_key', '');
        if (strlen($visitor_hash_key) < 32) {
            return $this->BuildCheck('visitor_hash_key', self::STATUS_BLOCKED, 'Click tracking requires an independent visitor hash key with at least 32 characters.');
        }

        return $this->BuildCheck('visitor_hash_key', self::STATUS_PASS, 'The independent visitor hash key is configured.');
    }

    private function DestinationHostsCheck(): array
    {
        if (!(bool) config('affiliate.features.media_library', false)) {
            return $this->BuildCheck('destination_hosts', self::STATUS_UNKNOWN, 'The media library is disabled; destination host validation is not enabled.');
        }

        return $this->BuildCheck('destination_hosts', self::STATUS_PASS, 'HTTPS destination validation is enabled; no destination host allowlist is required.');
    }

    private function AffiliateProfileOwnershipCheck(): array
    {
        if (!Schema::hasTable('affiliate_profiles')) {
            return $this->BuildCheck(
                'affiliate_profile_ownership',
                self::STATUS_UNKNOWN,
                'The Affiliate profile table is not installed in this environment.'
            );
        }

        try {
            $duplicate_owners_exist = DB::table('affiliate_profiles')
                ->whereNotNull('user_id')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) > 1')
                ->exists();
        } catch (Throwable) {
            return $this->BuildCheck(
                'affiliate_profile_ownership',
                self::STATUS_BLOCKED,
                'Affiliate profile ownership safety could not be established.'
            );
        }

        if ($duplicate_owners_exist) {
            return $this->BuildCheck(
                'affiliate_profile_ownership',
                self::STATUS_BLOCKED,
                'Duplicate Affiliate profile owners exist; the ownership constraint cannot be enabled safely.'
            );
        }

        return $this->BuildCheck(
            'affiliate_profile_ownership',
            self::STATUS_PASS,
            'Affiliate profile ownership has no duplicate user records.'
        );
    }

    private function MailDeliveryCheck(): array
    {
        return $this->BuildCheck('mail_delivery', self::STATUS_UNKNOWN, 'SMTP/provider delivery, expiry, resend, bounce, and failure handling require external evidence.');
    }

    private function SchedulerHostCheck(): array
    {
        return $this->BuildCheck('scheduler_host', self::STATUS_UNKNOWN, 'Application schedules exist; host execution, alerting, and responder evidence require external verification.');
    }

    private function AccessReviewCheck(): array
    {
        return $this->BuildCheck('access_review', self::STATUS_UNKNOWN, 'Least-privilege access list, exception approvals, and periodic review ownership require management evidence.');
    }

    private function FinancePolicyCheck(): array
    {
        return $this->BuildCheck('finance_policy', self::STATUS_UNKNOWN, 'Refund, clawback, negative-balance, collection, write-off, tax, and transfer-evidence policy approvals require Finance evidence.');
    }

    private function BuildCheck(string $name, string $status, string $message): array
    {
        return [
            'name' => $name,
            'status' => $status,
            'message' => $message,
        ];
    }
}
