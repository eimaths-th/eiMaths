@extends('layouts.app')
@section('content')
<div class="container py-3">
    <a class="btn btn-outline-secondary mb-3" href="{{ route('activity-logs.index', ['category' => $log['category']]) }}"><i class="fas fa-arrow-left mr-1" aria-hidden="true"></i>{{ __('activity-log.back') }}</a>
    <div class="card card-outline card-primary">
        <div class="card-header"><h1 class="h4 mb-0">{{ __('activity-log.details') }} #{{ $log['id'] }}</h1></div>
        <div class="card-body">
            <dl class="row">
                @foreach(['time' => $log['created_at'], 'actor' => $log['actor'], 'branch' => ($log['related_branch'] ? $log['related_branch'].' → ' : '').$log['branch'], 'action' => __('activity-log.actions.'.str_replace('.', '_', $log['action'])), 'outcome' => __('activity-log.outcomes.'.$log['outcome']), 'record' => $log['subject'].($log['subject_id'] ? ' #'.$log['subject_id'] : ''), 'ip' => $log['ip'], 'browser' => $log['browser']] as $label => $value)
                    <dt class="col-sm-3">{{ __('activity-log.'.$label) }}</dt><dd class="col-sm-9 text-break">{{ $value ?: '—' }}</dd>
                @endforeach
            </dl>
            @if(!empty($log['metadata']['password_changed']))<div class="alert alert-info">{{ __('activity-log.password_changed') }}</div>@endif
            @if(!empty($log['metadata']['reason']))<p><strong>{{ __('activity-log.reason') }}:</strong> {{ in_array($log['metadata']['reason'], ['account_disabled', 'branch_missing', 'branch_inactive'], true) ? __('activity-log.reasons.'.$log['metadata']['reason']) : $log['metadata']['reason'] }}</p>@endif
            @if(!empty($log['metadata']['status']))<p>{{ __('activity-log.http_status') }}: {{ $log['metadata']['status'] }}</p>@endif
            @php($fields = array_unique(array_merge(array_keys($log['before']), array_keys($log['after']))))
            @if($fields)
            <div class="table-responsive"><table class="table table-bordered"><thead class="bg-light"><tr><th>{{ __('activity-log.field') }}</th><th>{{ __('activity-log.before') }}</th><th>{{ __('activity-log.after') }}</th></tr></thead><tbody>
                @foreach($fields as $field)
                <tr><th scope="row">{{ \Illuminate\Support\Facades\Lang::has('activity-log.fields.'.$field) ? __('activity-log.fields.'.$field) : str_replace('_', ' ', $field) }}</th>@foreach(['before', 'after'] as $side)<td class="text-break" style="white-space:pre-wrap">{{ array_key_exists($field, $log[$side]) ? (is_array($log[$side][$field]) ? implode(', ', $log[$side][$field]) : ($log[$side][$field] ?? '—')) : '—' }}</td>@endforeach</tr>
                @endforeach
            </tbody></table></div>
            @else<p class="text-muted">{{ __('activity-log.no_changes') }}</p>@endif
        </div>
    </div>
</div>
@endsection
