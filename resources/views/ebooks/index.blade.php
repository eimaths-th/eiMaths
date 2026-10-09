@extends($layout)
@section('content')
@php
    $statusNames = __('ebooks.status');
    $statusColors = ['missing' => 'secondary', 'queued' => 'info', 'processing' => 'info', 'ready' => 'warning', 'failed' => 'danger', 'published' => 'success'];
    $dateLocal = fn ($value) => $value ? \Carbon\Carbon::parse($value, config('app.timezone'))->timezone('Asia/Bangkok')->format('d/m/Y H:i:s') : '—';
    $dateUtc = fn ($value) => $value ? \Carbon\Carbon::parse($value, 'UTC')->timezone('Asia/Bangkok')->format('d/m/Y H:i:s') : '—';
@endphp
<style>
.eb-workspace{color:#263b50;max-width:1800px;padding:24px;margin:0 auto}.eb-workspace h1{font-size:27px;font-weight:600;margin:0}.eb-subtitle{color:#6c7b89;font-size:15px;margin-top:4px}.eb-tabs{gap:6px;margin:22px 0}.eb-tabs .nav-link{border-radius:7px;padding:10px 17px}.eb-workspace .card{border:1px solid #e3e8ed;border-radius:10px;box-shadow:0 2px 7px #23384b08}.eb-workspace .card-header{padding:16px 20px;background:#fff;border-radius:10px 10px 0 0;font-weight:600}.eb-workspace .card-body{padding:20px}.eb-workspace .table th{white-space:nowrap;font-size:13px;color:#576b7d;background:#f7f9fb;border-top:0}.eb-workspace .table td{vertical-align:middle;padding-top:15px;padding-bottom:15px}.eb-workspace .badge{font-weight:500;padding:7px 10px;border-radius:20px}.eb-workspace label{font-size:14px;font-weight:600}.eb-code{font-size:17px;font-weight:600;color:#1f4770}.eb-help{color:#758797;font-size:13px}.eb-stat{padding:16px 18px;background:#fff;border:1px solid #e3e8ed;border-radius:9px;height:100%}.eb-stat strong{font-size:25px;display:block}.eb-upload{padding:28px;border:2px dashed #cbd7e1;background:#f8fafc;border-radius:10px}.eb-session{font-family:monospace;font-size:12px}.eb-form-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.eb-workspace .pagination{margin-bottom:0}.eb-history-file{max-width:250px;overflow-wrap:anywhere}
.eb-capture-status{display:inline-block;padding:7px 11px;border:1px solid #f9a8d4;border-radius:7px;background:#fce7f3;color:#9d174d;font-weight:600;line-height:1.5}
</style>
<div class="eb-workspace">
    <h1><i class="fas fa-book-open text-primary mr-2"></i>{{ __('ebooks.menu') }} <span style="font-weight:400">{{ __('ebooks.title') }}</span></h1>
    <div class="eb-subtitle">{{ __('ebooks.subtitle') }}</div>
    <nav class="nav nav-pills eb-tabs" aria-label="{{ __('ebooks.navigation') }}">
        @if($manage)
            @foreach(['library', 'import', 'history'] as $key)
                <a class="nav-link {{ $section === $key ? 'active' : '' }}" href="{{ route($routePrefix.'.index', ['section' => $key]) }}">{{ __('ebooks.tabs.'.$key) }}</a>
            @endforeach
        @endif
        @if($viewActivity)
            <a class="nav-link {{ $section === 'activity' ? 'active' : '' }}" href="{{ route($routePrefix.'.index', ['section' => 'activity']) }}">{{ __('ebooks.tabs.activity') }}</a>
        @endif
    </nav>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    @include('ebooks.'.$section)
</div>
@endsection
