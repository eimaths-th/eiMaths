<?php

namespace App\Domain\Affiliate\ValueObjects;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;

final class AffiliateAnalyticsRange
{
    private const TIMEZONE = 'Asia/Bangkok';
    private const INVALID_DATE_MESSAGE = 'Affiliate analytics dates must use the Y-m-d format.';
    private const FUTURE_DATE_MESSAGE = 'Affiliate analytics dates cannot be in the future.';
    private const REVERSED_RANGE_MESSAGE = 'Affiliate analytics start date must not be after the end date.';
    private const RANGE_TOO_LONG_MESSAGE = 'Affiliate analytics date ranges cannot exceed 366 days.';

    private function __construct(
        private readonly CarbonImmutable $from_date,
        private readonly CarbonImmutable $to_date,
    ) {
    }

    public static function Default(?DateTimeInterface $now = null): self
    {
        $local_now = self::LocalNow($now);

        return new self($local_now->startOfDay()->subDays(29), $local_now->startOfDay());
    }

    public static function FromStrings(string $from, string $to, ?DateTimeInterface $now = null): self
    {
        $from_date = self::ParseDate($from);
        $to_date = self::ParseDate($to);
        $local_today = self::LocalNow($now)->startOfDay();

        if ($from_date->greaterThan($to_date)) {
            throw new InvalidArgumentException(self::REVERSED_RANGE_MESSAGE);
        }
        if ($to_date->greaterThan($local_today)) {
            throw new InvalidArgumentException(self::FUTURE_DATE_MESSAGE);
        }
        if ($from_date->diffInDays($to_date) + 1 > 366) {
            throw new InvalidArgumentException(self::RANGE_TOO_LONG_MESSAGE);
        }

        return new self($from_date, $to_date);
    }

    public function FromDate(): CarbonImmutable
    {
        return $this->from_date;
    }

    public function ToDate(): CarbonImmutable
    {
        return $this->to_date;
    }

    public function EventFromUtc(): CarbonImmutable
    {
        return $this->from_date->setTimezone('UTC');
    }

    public function EventToUtcExclusive(): CarbonImmutable
    {
        return $this->to_date->addDay()->setTimezone('UTC');
    }

    public function CommissionFromLocal(): CarbonImmutable
    {
        return $this->from_date;
    }

    public function CommissionToLocalExclusive(): CarbonImmutable
    {
        return $this->to_date->addDay();
    }

    public function Dates(): array
    {
        $dates = [];
        for ($date = $this->from_date; $date->lessThanOrEqualTo($this->to_date); $date = $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }

    public function ToArray(): array
    {
        return [
            'from' => $this->from_date->format('Y-m-d'),
            'to' => $this->to_date->format('Y-m-d'),
            'timezone' => self::TIMEZONE,
        ];
    }

    private static function LocalNow(?DateTimeInterface $now): CarbonImmutable
    {
        return ($now === null ? CarbonImmutable::now(self::TIMEZONE) : CarbonImmutable::instance($now))
            ->setTimezone(self::TIMEZONE);
    }

    private static function ParseDate(string $value): CarbonImmutable
    {
        if (! preg_match('/\A\d{4}-\d{2}-\d{2}\z/D', $value)) {
            throw new InvalidArgumentException(self::INVALID_DATE_MESSAGE);
        }

        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value, self::TIMEZONE);
        } catch (\Throwable) {
            throw new InvalidArgumentException(self::INVALID_DATE_MESSAGE);
        }

        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException(self::INVALID_DATE_MESSAGE);
        }

        return $date;
    }
}
