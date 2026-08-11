<?php

namespace Tests\Unit\Affiliate;

use App\Domain\Affiliate\ValueObjects\AffiliateAnalyticsRange;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateAnalyticsRangeTest extends TestCase
{
    #[Test]
    public function AcceptsExactSevenThirtyNinetyAndThreeHundredSixtySixDayRanges(): void
    {
        $now = CarbonImmutable::parse('2027-01-02 12:00:00', 'Asia/Bangkok');

        foreach ([
            ['2026-08-05', '2026-08-11', 7],
            ['2026-07-13', '2026-08-11', 30],
            ['2026-05-14', '2026-08-11', 90],
            ['2026-01-01', '2027-01-01', 366],
        ] as [$from, $to, $days]) {
            $range = AffiliateAnalyticsRange::FromStrings($from, $to, $now);

            $this->assertCount($days, $range->Dates());
            $this->assertSame($from, $range->FromDate()->format('Y-m-d'));
            $this->assertSame($to, $range->ToDate()->format('Y-m-d'));
        }
    }

    #[Test]
    public function RejectsFutureReversedMalformedAndOverlongRanges(): void
    {
        $now = CarbonImmutable::parse('2026-08-11 12:00:00', 'Asia/Bangkok');

        foreach ([
            ['2026-08-12', '2026-08-12'],
            ['2026-08-11', '2026-08-10'],
            ['2026-01-01', '2027-01-02'],
            ['2026-8-1', '2026-08-01'],
        ] as [$from, $to]) {
            $this->expectException(InvalidArgumentException::class);
            AffiliateAnalyticsRange::FromStrings($from, $to, $now);
        }
    }

    #[Test]
    public function ConvertsBangkokMidnightToUtcBoundaries(): void
    {
        $range = AffiliateAnalyticsRange::FromStrings(
            '2026-08-11',
            '2026-08-11',
            CarbonImmutable::parse('2026-08-11 12:00:00', 'Asia/Bangkok'),
        );

        $this->assertSame('2026-08-10T17:00:00.000000Z', $range->EventFromUtc()->toISOString());
        $this->assertSame('2026-08-11T17:00:00.000000Z', $range->EventToUtcExclusive()->toISOString());
        $this->assertSame('Asia/Bangkok', $range->CommissionFromLocal()->getTimezone()->getName());
        $this->assertSame(['from' => '2026-08-11', 'to' => '2026-08-11', 'timezone' => 'Asia/Bangkok'], $range->ToArray());
    }

    #[Test]
    public function DefaultRangeIncludesCurrentBangkokDateAndPrecedingTwentyNineDates(): void
    {
        $range = AffiliateAnalyticsRange::Default(CarbonImmutable::parse('2026-08-11 12:00:00', 'UTC'));

        $this->assertSame('2026-07-13', $range->FromDate()->format('Y-m-d'));
        $this->assertSame('2026-08-11', $range->ToDate()->format('Y-m-d'));
        $this->assertCount(30, $range->Dates());
    }
}
