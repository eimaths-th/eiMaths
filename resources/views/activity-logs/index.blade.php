@extends('layouts.app')
@section('content')
<div class="container-fluid py-3">
    <h1 class="h3"><i class="fas fa-history text-primary mr-2" aria-hidden="true"></i>{{ __('activity-log.title') }}</h1>
    <p class="text-muted">{{ __('activity-log.description') }}</p>
    <div class="card card-outline card-primary">
        <div class="card-header p-0">
            <nav class="nav nav-tabs" aria-label="{{ __('activity-log.category') }}">
                @foreach(['authentication' => 'fa-sign-in-alt', 'activity' => 'fa-clipboard-list'] as $tab => $icon)
                    <a class="nav-link px-4 py-3 {{ $category === $tab ? 'active font-weight-bold' : '' }}" href="{{ route('activity-logs.index', ['category' => $tab]) }}" @if($category === $tab) aria-current="page" @endif>
                        <i class="fas {{ $icon }} mr-2" aria-hidden="true"></i>{{ __('activity-log.tabs.'.$tab) }}
                    </a>
                @endforeach
            </nav>
        </div>
        <div class="card-body">
            @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
            <form method="get" action="{{ route('activity-logs.index') }}">
                <input type="hidden" name="category" value="{{ $category }}">
                <div class="form-row">
                    @foreach(['from', 'to'] as $key)
                    <div class="form-group col-md-2 col-6"><label for="log-{{ $key }}">{{ __('activity-log.'.$key) }}</label><input id="log-{{ $key }}" name="{{ $key }}" type="date" class="form-control" value="{{ $filters[$key] ?? '' }}"></div>
                    @endforeach
                    @if($crossBranch)
                    <div class="form-group col-md-3"><label for="log-branch">{{ __('activity-log.branch') }}</label><select id="log-branch" name="branch_id" class="form-control"><option value="">{{ __('activity-log.all') }}</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
                    @endif
                    <div class="form-group col-md-3"><label for="log-actor">{{ __('activity-log.actor') }}</label><select id="log-actor" name="actor_id" class="form-control"><option value="">{{ __('activity-log.all') }}</option>@foreach($actors as $actor)<option value="{{ $actor->actor_id }}" @selected(($filters['actor_id'] ?? '') == $actor->actor_id)>{{ $actor->name }}</option>@endforeach</select></div>
                    @if($category === 'activity')
                    <div class="form-group col-md-2"><label for="log-module">{{ __('activity-log.module') }}</label><select id="log-module" name="module" class="form-control"><option value="">{{ __('activity-log.all') }}</option>@foreach(['users', 'branches', 'students', 'teaching', 'finance'] as $module)<option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ __('activity-log.modules.'.$module) }}</option>@endforeach</select></div>
                    @endif
                </div>
                <div class="form-row align-items-end">
                    <div class="form-group col-md-3"><label for="log-action">{{ __('activity-log.action') }}</label><select id="log-action" name="action" class="form-control"><option value="">{{ __('activity-log.all') }}</option>@foreach(($category === 'authentication' ? ['login', 'logout', 'login.failed', 'login.denied'] : ['created', 'updated', 'deleted', 'roles.changed', 'permissions.changed', 'branch.transferred', 'request.failed']) as $action)<option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ __('activity-log.actions.'.str_replace('.', '_', $action)) }}</option>@endforeach</select></div>
                    <div class="form-group col-md-3"><label for="log-outcome">{{ __('activity-log.outcome') }}</label><select id="log-outcome" name="outcome" class="form-control"><option value="">{{ __('activity-log.all') }}</option>@foreach(['success', 'failed', 'denied'] as $outcome)<option value="{{ $outcome }}" @selected(($filters['outcome'] ?? '') === $outcome)>{{ __('activity-log.outcomes.'.$outcome) }}</option>@endforeach</select></div>
                    <div class="form-group col-md-6"><button class="btn btn-primary mr-2" type="submit"><i class="fas fa-search mr-1" aria-hidden="true"></i>{{ __('activity-log.filter') }}</button><a class="btn btn-outline-secondary" href="{{ route('activity-logs.index', ['category' => $category]) }}">{{ __('activity-log.reset') }}</a></div>
                </div>
            </form>
            <p class="small text-muted">{{ __('activity-log.timezone') }} · {{ __('activity-log.count', ['count' => number_format($logs->total())]) }}</p>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="bg-light"><tr>@foreach(['time', 'actor', 'branch', 'action', 'outcome', 'details'] as $heading)<th scope="col">{{ __('activity-log.'.$heading) }}</th>@endforeach</tr></thead>
                    <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log['created_at'] }}</td><td>{{ $log['actor'] }}</td>
                            <td>@if($log['related_branch'])<span>{{ $log['related_branch'] }} → </span>@endif{{ $log['branch'] }}</td>
                            <td>{{ __('activity-log.actions.'.str_replace('.', '_', $log['action'])) }}<div class="small text-muted">{{ __('activity-log.modules.'.$log['module']) }} @if($log['subject_id']) · {{ $log['subject'] }} #{{ $log['subject_id'] }}@endif</div></td>
                            <td><span class="badge badge-{{ $log['outcome'] === 'success' ? 'success' : ($log['outcome'] === 'denied' ? 'warning' : 'danger') }}">{{ __('activity-log.outcomes.'.$log['outcome']) }}</span></td>
                            <td><a class="btn btn-sm btn-outline-primary text-nowrap" href="{{ route('activity-logs.show', $log['id']) }}">{{ __('activity-log.details') }}<span class="sr-only"> #{{ $log['id'] }}</span></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-history fa-2x d-block mb-3" aria-hidden="true"></i>{{ __('activity-log.empty') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $logs->links('pagination::bootstrap-4') }}
        </div>
    </div>
    <p class="small text-muted">{{ __('activity-log.scope_note') }}</p>
</div>
@endsection
