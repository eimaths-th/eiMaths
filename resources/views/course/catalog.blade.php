@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <h1>{{ __('course.catalog.title') }}</h1>
            <p class="text-muted mb-0">{{ __('course.catalog.read_only') }}</p>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('course.catalog.course') }}</th>
                                <th>{{ __('course.catalog.category') }}</th>
                                <th>{{ __('course.catalog.level') }}</th>
                                <th class="text-right">{{ __('course.catalog.price') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($courses as $course)
                                <tr>
                                    <td>{{ $course->title }}</td>
                                    <td>{{ $course->category?->name ?? '—' }}</td>
                                    <td>{{ $course->courseLevel?->title ?? '—' }}</td>
                                    <td class="text-right">{{ number_format((float) $course->price, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('course.catalog.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($courses->hasPages())
                    <div class="card-footer">{{ $courses->links('pagination::bootstrap-4') }}</div>
                @endif
            </div>
        </div>
    </section>
@endsection
