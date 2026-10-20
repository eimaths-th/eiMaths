@extends('layouts.app')
@section('content')
<section class="content-header"><h1>{{ __('content-management.faq.statistics.title') }}</h1></section>
<section class="content"><div class="card"><div class="card-body">
    <p>{{ __('content-management.faq.statistics.metric') }}</p>
    @if(!$validation_failed)<p>Asia/Bangkok: {{ $range->start_date }} - {{ $range->end_date }}</p>@endif
    <form method="GET" action="{{ route('faqs.statistics') }}" class="mb-4">
        <label for="faq-range">{{ __('content-management.faq.statistics.range') }}</label>
        <select id="faq-range" name="range" class="form-control mb-2" @error('range') aria-invalid="true" aria-describedby="faq-range-error" @enderror>
            @foreach(['7d' => 'seven_days', '30d' => 'thirty_days', 'custom' => 'custom'] as $range_key => $label_key)
                <option value="{{ $range_key }}" @selected(($filter_values['range'] ?? $range->range) === $range_key)>{{ __('content-management.faq.statistics.'.$label_key) }}</option>
            @endforeach
        </select>
        @foreach(['start_date', 'end_date'] as $date_key)
            <label for="faq-{{ str_replace('_', '-', $date_key) }}">{{ __('content-management.faq.statistics.'.$date_key) }}</label>
            <input class="form-control mb-2" type="date" id="faq-{{ str_replace('_', '-', $date_key) }}" name="{{ $date_key }}"
                value="{{ $filter_values[$date_key] ?? $range->{$date_key} }}" @error($date_key) aria-invalid="true" aria-describedby="faq-{{ str_replace('_', '-', $date_key) }}-error" @enderror>
            @error($date_key)<p role="alert" id="faq-{{ str_replace('_', '-', $date_key) }}-error">{{ $message }}</p>@enderror
        @endforeach
        @error('range')<p id="faq-range-error" role="alert">{{ $message }}</p>@enderror
        @error('page')<p role="alert">{{ $message }}</p>@enderror
        <button class="btn btn-primary" type="submit">{{ __('content-management.faq.statistics.apply') }}</button>
    </form>
    @if($report_error)
        <p role="alert" class="alert alert-danger">{{ __('content-management.faq.statistics.unavailable') }}</p>
    @elseif(!$validation_failed)
        <div class="table-responsive"><table class="table table-bordered" data-faq-statistics="true">
            <thead><tr><th>{{ __('content-management.faq.table.question') }}</th><th>{{ __('content-management.faq.table.category') }}</th><th>{{ __('content-management.faq.statistics.reads') }}</th></tr></thead>
            <tbody>@forelse($rows as $row)
                <tr data-faq-id="{{ $row['faq_reference_id'] }}"><td>{{ $row['question'] }}
                    @if($row['is_deleted'])<span class="badge badge-secondary">{{ __('content-management.faq.statistics.deleted') }}</span>@endif
                </td><td>{{ $row['category'] }}</td><td>{{ $row['read_count'] }}</td></tr>
            @empty
                <tr><td colspan="3">{{ __('content-management.faq.statistics.empty') }}</td></tr>
            @endforelse</tbody>
        </table></div>
        {{ $rows->links() }}
    @endif
</div></div></section>
@endsection
