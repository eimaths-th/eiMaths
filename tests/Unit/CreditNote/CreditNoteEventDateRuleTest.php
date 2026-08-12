<?php

namespace Tests\Unit\CreditNote;

use App\Domain\CreditNote\Rules\CreditNoteEventDateRule;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteEventDateRuleTest extends TestCase
{
    #[Test]
    public function AcceptsIssueMonthAndPreviousMonthWithReason(): void
    {
        $issue_date = CarbonImmutable::parse('2026-08-12', 'Asia/Bangkok');

        CreditNoteEventDateRule::Validate(
            CarbonImmutable::parse('2026-08-01', 'Asia/Bangkok'),
            $issue_date,
            null
        );
        CreditNoteEventDateRule::Validate(
            CarbonImmutable::parse('2026-07-31', 'Asia/Bangkok'),
            $issue_date,
            'Late approved adjustment'
        );

        $this->addToAssertionCount(2);
    }

    #[Test]
    public function RejectsFutureOlderAndUnexplainedPreviousMonthDates(): void
    {
        $issue_date = CarbonImmutable::parse('2026-08-12', 'Asia/Bangkok');

        foreach ([
            [CarbonImmutable::parse('2026-08-13', 'Asia/Bangkok'), null],
            [CarbonImmutable::parse('2026-06-30', 'Asia/Bangkok'), 'reason'],
            [CarbonImmutable::parse('2026-07-31', 'Asia/Bangkok'), '   '],
        ] as [$event_date, $late_reason]) {
            $this->expectException(\InvalidArgumentException::class);
            CreditNoteEventDateRule::Validate($event_date, $issue_date, $late_reason);
        }
    }
}
