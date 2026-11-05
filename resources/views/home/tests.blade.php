@extends('layouts.quiz_layout', ['test_preview_page' => true])
@section('title', __('quiz-management.preview.title'))

@section('content')
    @include('quiz-results.partials.list', ['can_view_answers' => $can_view_answers ?? false, 'answer_route_name' => $answer_route_name ?? 'results.show'])
@endsection
