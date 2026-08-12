<?php

namespace App\Domain\CreditNote\Rules;

use Carbon\CarbonImmutable;

final class CreditNoteEventDateRule
{
    public static function Validate(
        CarbonImmutable $event_date,
        CarbonImmutable $issue_date,
        ?string $late_issue_reason,
    ): void {
        $timezone = (string) config('app.timezone', 'Asia/Bangkok');
        $event_date = $event_date->setTimezone($timezone)->startOfDay();
        $issue_date = $issue_date->setTimezone($timezone)->startOfDay();

        if ($event_date->greaterThan($issue_date)) {
            throw new \InvalidArgumentException('Credit Note event date cannot be in the future.');
        }

        if ($event_date->year === $issue_date->year && $event_date->month === $issue_date->month) {
            return;
        }

        $previous_month = $issue_date->subMonthNoOverflow();
        if (
            $event_date->year === $previous_month->year
            && $event_date->month === $previous_month->month
            && trim((string) $late_issue_reason) !== ''
        ) {
            return;
        }

        throw new \InvalidArgumentException('Credit Note event date must be in the Issue month or explained previous month.');
    }
}
