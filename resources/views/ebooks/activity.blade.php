<div class="card">
    <div class="card-header">{{ __('ebooks.tabs.activity') }} <span class="eb-help ml-2">{{ __('ebooks.activity.subtitle') }}</span></div>
    <div class="card-body border-bottom">
        <form method="get" action="{{ route($routePrefix.'.index') }}" class="row align-items-end">
            <input type="hidden" name="section" value="activity">
            <div class="col-xl-2 col-md-4 form-group"><label for="activity-student">{{ __('common.fields.student_code') }}</label><input id="activity-student" class="form-control" name="student" value="{{ request('student') }}" placeholder="{{ __('common.fields.student_code') }}"></div>
            <div class="col-xl-2 col-md-4 form-group"><label for="activity-book">{{ __('common.fields.book') }}</label><select id="activity-book" class="form-control" name="book"><option value="">{{ __('ebooks.filters.all_books') }}</option>@foreach($books as $b)<option value="{{ $b->id }}" @selected(request('book') == $b->id)>{{ $b->code }}</option>@endforeach</select></div>
            <div class="col-xl-3 col-md-4 form-group"><label for="activity-action">{{ __('ebooks.fields.activity') }}</label><select id="activity-action" name="event_action" class="form-control"><option value="">{{ __('ebooks.filters.all_activities') }}</option>@foreach(array_merge(['login', 'logout', 'login_blocked', 'book_open', 'page_open', 'work_saved'], \App\Domain\Ebook\EventIngestor::ACTIONS) as $action)<option value="{{ $action }}" @selected(request('event_action') === $action)>{{ \App\Domain\Ebook\LogLabels::action($action) }}</option>@endforeach</select></div>
            <div class="col-xl-2 col-md-4 form-group"><label for="activity-from">{{ __('ebooks.fields.from') }}</label><input id="activity-from" name="from" type="date" class="form-control" value="{{ request('from') }}"></div>
            <div class="col-xl-2 col-md-4 form-group"><label for="activity-to">{{ __('ebooks.fields.to') }}</label><input id="activity-to" name="to" type="date" class="form-control" value="{{ request('to') }}"></div>
            <div class="col-12 eb-form-actions"><button class="btn btn-primary"><i class="fas fa-search mr-1"></i>{{ __('common.actions.search') }}</button><a class="btn btn-light" href="{{ route($routePrefix.'.index', ['section' => 'activity']) }}">{{ __('ebooks.actions.clear_filters') }}</a><span class="eb-help ml-2">{{ trans_choice('ebooks.counts.records', $events->total(), ['count' => number_format($events->total())]) }}</span></div>
        </form>
    </div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th class="pl-4">{{ __('ebooks.fields.occurred_at') }}</th><th>{{ __('ebooks.fields.received_at') }}</th><th>{{ __('ebooks.fields.student') }}</th><th>{{ __('ebooks.fields.book_page') }}</th><th>{{ __('ebooks.fields.activity') }}</th><th>{{ __('ebooks.fields.details') }}</th><th>{{ __('ebooks.fields.session') }}</th></tr></thead><tbody>
    @forelse($events as $e)
        <tr><td class="pl-4 text-nowrap">{{ $dateUtc($e->occurred_at) }}<div class="eb-help">{{ __($e->source === 'client' ? 'ebooks.activity.client_time' : 'ebooks.activity.server_time') }}</div></td><td class="text-nowrap">{{ $dateLocal($e->received_at) }}</td>
            <td>{{ $e->student_code }}<div class="eb-help">{{ $e->student_name }}</div></td><td>{{ $e->book_code ?: '—' }}@if($e->page_number)<div class="eb-help">{{ __('ebooks.page_number', ['number' => $e->page_number]) }}</div>@endif</td>
            <td><span @class(['eb-capture-status' => in_array($e->action, ['capture_detected', 'capture_stopped', 'screenshot_detected'], true)])>{{ \App\Domain\Ebook\LogLabels::action($e->action) }}</span></td><td>{{ \App\Domain\Ebook\LogLabels::detail($e, $pageNumbers) }}</td>
            <td>@if(in_array($e->session_origin, ['client', 'server']))<span class="eb-session" title="{{ $e->session_id }}">{{ substr($e->session_id ?? '', 0, 8) ?: '—' }}</span>@else<span class="eb-help">{{ __('ebooks.activity.unknown_session') }}</span>@endif
                @if($e->received_session_id && ($e->received_session_id !== $e->session_id || in_array($e->session_origin, ['legacy', 'unknown'])))<div class="eb-help" title="{{ $e->received_session_id }}">{{ __('ebooks.activity.received_via', ['session' => substr($e->received_session_id, 0, 8)]) }}</div>@endif</td></tr>
    @empty<tr><td colspan="7" class="text-center text-muted p-5">{{ __('ebooks.activity.empty') }}</td></tr>@endforelse
    </tbody></table></div><div class="card-body">{{ $events->links('ebooks.pagination') }}</div>
</div>
