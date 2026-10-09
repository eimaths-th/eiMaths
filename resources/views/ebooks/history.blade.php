<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap"><span>{{ __('ebooks.tabs.history') }}</span>
        <form method="get" class="eb-form-actions"><input type="hidden" name="section" value="history"><label for="history-book" class="sr-only">{{ __('common.fields.book') }}</label><select id="history-book" name="book" class="form-control" style="width:160px"><option value="">{{ __('ebooks.filters.all_books') }}</option>@foreach($books as $b)<option value="{{ $b->id }}" @selected(request('book') == $b->id)>{{ $b->code }}</option>@endforeach</select><button class="btn btn-primary">{{ __('common.actions.search') }}</button></form>
    </div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th class="pl-4">{{ __('ebooks.fields.book_edition') }}</th><th>{{ __('ebooks.fields.original_file') }}</th><th>{{ __('ebooks.fields.imported_by_at') }}</th><th>{{ __('ebooks.fields.status_pages') }}</th><th>{{ __('ebooks.fields.first_published') }}</th><th>{{ __('common.actions.manage') }}</th></tr></thead><tbody>
    @forelse($history as $v)
        @php($editionStatus = (int)$v->published_version_id === (int)$v->id ? 'published' : $v->status)
        <tr><td class="pl-4"><span class="eb-code">{{ $v->code }}</span><div class="eb-help">{{ __('ebooks.edition', ['number' => $v->number]) }}</div></td>
            <td class="eb-history-file">{{ $v->original_filename ?: __('ebooks.history.legacy_unknown') }}@if($v->original_size)<div class="eb-help">{{ number_format($v->original_size / 1024 / 1024, 2) }} MB</div>@endif</td>
            <td>{{ $v->uploaded_by_name ?: __('ebooks.history.legacy_unknown') }}<div class="eb-help">{{ $dateLocal($v->created_at) }}</div></td>
            <td><span class="badge badge-{{ $statusColors[$editionStatus] }}">{{ $statusNames[$editionStatus] }}</span><div class="eb-help mt-1">{{ trans_choice('ebooks.counts.pages', $v->page_count) }}</div>
                @if((int)$v->published_version_id === (int)$v->id)<div class="text-success small mt-1">{{ __('ebooks.history.current_edition') }}</div>@elseif($v->published_at)<div class="eb-help mt-1">{{ __('ebooks.history.retained_edition') }}</div>@endif
                @if($v->error)<details class="text-danger small mt-2"><summary>{{ __('ebooks.history.error_details') }}</summary><div style="max-width:320px;overflow-wrap:anywhere">{{ \App\Domain\Ebook\LogLabels::conversionError($v->error) }}</div></details>@endif
            </td><td>{{ $dateLocal($v->published_at) }}@if($v->published_by_name)<div class="eb-help">{{ $v->published_by_name }}</div>@endif</td>
            <td>@if($v->status === 'ready')<div class="eb-form-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route($routePrefix.'.preview', $v->id) }}">{{ __('ebooks.actions.preview') }}</a>
                @if((int)$v->published_version_id !== (int)$v->id)<form method="post" action="{{ route($routePrefix.'.publish', $v->id) }}">@csrf<button class="btn btn-sm btn-success">{{ __('ebooks.actions.publish') }}</button></form>@endif</div>@endif</td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted p-5">{{ __('ebooks.history.empty') }}</td></tr>@endforelse
    </tbody></table></div><div class="card-body">{{ $history->links('ebooks.pagination') }}</div>
</div>
<div class="card mt-4">
    <div class="card-header">{{ __('ebooks.history.management_title') }}</div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th class="pl-4">{{ __('common.fields.time') }}</th><th>{{ __('ebooks.fields.operator') }}</th><th>{{ __('ebooks.fields.book_edition') }}</th><th>{{ __('ebooks.fields.activity') }}</th></tr></thead><tbody>
    @forelse($adminEvents as $e)<tr><td class="pl-4">{{ $dateUtc($e->occurred_at) }}</td><td>{{ $e->actor_name }}</td><td>{{ $e->code }} / {{ $e->number }}</td><td>{{ __($e->action === 'imported' ? 'ebooks.history.imported' : 'ebooks.history.published') }}</td></tr>
    @empty<tr><td colspan="4" class="text-center text-muted p-4">{{ __('ebooks.history.management_empty') }}</td></tr>@endforelse
    </tbody></table></div><div class="card-body">{{ $adminEvents->links('ebooks.pagination') }}</div>
</div>
