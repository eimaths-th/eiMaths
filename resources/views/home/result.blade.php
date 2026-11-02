@extends('layouts.quiz_layout', ['result_page' => true])
@section('title', $test->quiz->title)
@section('content')
    @php
        $question_count = $questions_count;
        $score = $test->result ?? 0;
        $test_user = match ($test->type_user) {
            'user' => $test->user, 'student' => $test->student, default => null,
        };
        $duration_seconds = $test->time_spent;
    @endphp
    <header class="result-heading">
        <div class="result-identity">
        <p class="result-label">ผลการทำแบบทดสอบ</p>
        <h1>{{ $test->quiz->title }}</h1>
        <p class="result-student">{{ $test_user?->nickname ?? $test_user?->name ?? 'ไม่พบชื่อผู้เรียน' }}
            @if ($test_user && ($test_user->email ?? $test_user->name))
                <span>{{ $test_user->email ?? $test_user->name }}</span>
            @endif
        </p>
        </div>
        <div class="result-score-panel">
            <dl>
                <dt>คะแนน</dt>
                <dd class="result-score">{{ $score }}/{{ $question_count }}</dd>
            </dl>
        </div>
        <dl class="result-summary">
            <div><dt>คิดเป็น</dt><dd>{{ $question_count > 0 ? number_format(($score / $question_count) * 100, 2) . '%' : '—' }}</dd></div>
            <div><dt>เวลาที่ใช้</dt><dd>@if ($duration_seconds === null) —
                @else
                    @if ($duration_seconds >= 3600){{ intdiv($duration_seconds, 3600) }} ชั่วโมง @endif
                    @if ($duration_seconds >= 60){{ intdiv($duration_seconds % 3600, 60) }} นาที @endif
                    {{ $duration_seconds % 60 }} วินาที
                @endif</dd></div>
            @if ($test->created_at)
                <div><dt>วันที่ทำแบบทดสอบ</dt><dd><time datetime="{{ $test->created_at->toIso8601String() }}">{{ $test->created_at->format('d/m/Y, H:i') }}</time></dd></div>
            @endif
        </dl>
    </header>
    <section class="result-review" aria-labelledby="result-review-title">
        <h2 id="result-review-title">ทบทวนคำตอบ</h2>
        @foreach ($results as $result)
            @include('home.result.answer', ['answer' => $result, 'question_number' => $loop->iteration, 'can_view_videos' => $canViewSolutionVideos ?? false])
        @endforeach
    </section>
@endsection
@push('scripts')
    <script type="text/javascript" async src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML"></script>
    <script>
        window.addEventListener('beforeunload', function(event) {
            event.preventDefault();
            event.returnValue = '';
        });
        window.addEventListener('popstate', function(event) {
            event.preventDefault();
            history.pushState(null, null, window.location.href);
            return false;
        });
        if (window.history && window.history.pushState) {
            window.history.replaceState(null, null, window.location.href);
            window.addEventListener('popstate', function() {
                window.history.replaceState(null, null, window.location.href);
            });
        }
        history.pushState(null, null, window.location.href);
    </script>
@endpush
