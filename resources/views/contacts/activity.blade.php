@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <h1 class="h4">{{ __('students.activity.title') }}: {{ $student->nickname ?? $student->name }} ({{ $student->code }})</h1>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h2 class="card-title">{{ __('students.activity.logins_heading') }}</h2>
            </div>
            <div class="card-body">
                @if ($login_events->count())
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('students.activity.event_time') }}</th>
                                <th scope="col">{{ __('students.activity.event_type') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($login_events as $login_event)
                                <tr>
                                    <td>{{ $login_event->occurred_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $login_event->event_type === \App\Models\StudentLoginEvent::LOGIN ? __('students.activity.event_login') : __('students.activity.event_logout') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $login_events->links() }}
                @else
                    <p class="mb-0" role="status">{{ __('students.activity.no_logins') }}</p>
                @endif
            </div>
        </div>

        @if ($quiz_attempts !== null)
            <div class="card card-primary">
                <div class="card-header">
                    <h2 class="card-title">{{ __('students.activity.quizzes_heading') }}</h2>
                </div>
                <div class="card-body">
                    @if ($quiz_attempts->count())
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('quiz-management.preview.attempted_at') }}</th>
                                    <th scope="col">{{ __('quiz-management.preview.quiz') }}</th>
                                    <th scope="col">{{ __('quiz-management.preview.score') }}</th>
                                    <th scope="col">{{ __('quiz-management.preview.duration') }}</th>
                                    @if ($can_view_answers)
                                        <th scope="col">{{ __('quiz-management.preview.view_answers') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($quiz_attempts as $quiz_attempt)
                                    @php
                                        $duration_seconds = (int) ($quiz_attempt->time_spent ?? 0);
                                        $quiz_title = $quiz_attempt->quiz->title ?? __('quiz-management.preview.not_available');
                                    @endphp
                                    <tr>
                                        <td>{{ $quiz_attempt->created_at?->format('d/m/Y H:i') ?? __('quiz-management.preview.not_available') }}</td>
                                        <td>{{ $quiz_title }}</td>
                                        <td>{{ $quiz_attempt->result ?? 0 }}/{{ $quiz_attempt->questions_count }}</td>
                                        <td>{{ sprintf('%02d:%02d:%02d', intdiv($duration_seconds, 3600), intdiv($duration_seconds % 3600, 60), $duration_seconds % 60) }}</td>
                                        @if ($can_view_answers)
                                            <td><a href="{{ route('backoffice.quiz-results.show', $quiz_attempt) }}">{{ __('quiz-management.preview.view_answers') }}</a></td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $quiz_attempts->links() }}
                    @else
                        <p class="mb-0" role="status">{{ __('students.activity.no_quizzes') }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
