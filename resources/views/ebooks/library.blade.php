<div class="row mb-4">
    @foreach([[__('ebooks.library.total_books'), $allVolumes->count()], [__('ebooks.status.published'), $allVolumes->where('status', 'published')->count()], [__('ebooks.status.missing'), $allVolumes->where('status', 'missing')->count()]] as [$label, $count])
    <div class="col-md-4 mb-2"><div class="eb-stat"><span class="eb-help">{{ $label }}</span><strong>{{ number_format($count) }} <small class="eb-help">{{ trans_choice('ebooks.library.book_unit', $count) }}</small></strong></div></div>
    @endforeach
</div>
<div class="card">
    <div class="card-body border-bottom">
        <form method="get" action="{{ route($routePrefix.'.index') }}" class="row align-items-end">
            <input type="hidden" name="section" value="library">
            <div class="col-lg-2 col-sm-6 form-group mb-lg-0"><label for="level">{{ __('common.fields.level') }}</label><select class="form-control" name="level" id="level"><option value="">{{ __('ebooks.filters.all_levels') }}</option>@foreach($volumes->unique('level_id') as $v)<option value="{{ $v['level_id'] }}" @selected(request('level') == $v['level_id'])>{{ $v['level'] }}</option>@endforeach</select></div>
            <div class="col-lg-2 col-sm-6 form-group mb-lg-0"><label for="term">{{ __('common.fields.term') }}</label><select class="form-control" name="term" id="term"><option value="">{{ __('ebooks.filters.all_terms') }}</option>@foreach($volumes->unique('term_id') as $v)<option value="{{ $v['term_id'] }}" @selected(request('term') == $v['term_id'])>{{ $v['term'] }}</option>@endforeach</select></div>
            <div class="col-lg-2 col-sm-6 form-group mb-lg-0"><label for="search">{{ __('ebooks.fields.book_code') }}</label><input class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="{{ __('ebooks.filters.code_example') }}"></div>
            <div class="col-lg-3 col-sm-6 form-group mb-lg-0"><label for="status">{{ __('common.fields.status') }}</label><select class="form-control" name="status" id="status"><option value="">{{ __('ebooks.filters.all_statuses') }}</option>@foreach($statusNames as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-lg-3 eb-form-actions"><button class="btn btn-primary"><i class="fas fa-search mr-1"></i>{{ __('common.actions.search') }}</button><a class="btn btn-light" href="{{ route($routePrefix.'.index') }}">{{ __('ebooks.actions.clear_filters') }}</a></div>
        </form>
    </div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th class="pl-4">{{ __('ebooks.fields.book_code') }}</th><th>{{ __('ebooks.fields.level_term') }}</th><th>{{ __('ebooks.fields.registration_sets') }}</th><th>{{ __('common.fields.status') }}</th><th>{{ __('ebooks.fields.latest_edition') }}</th><th>{{ __('common.actions.manage') }}</th></tr></thead><tbody>
    @forelse($library as $v)
        <tr><td class="pl-4"><span class="eb-code">{{ $v['code'] }}</span></td><td>{{ $v['level'] }} / {{ $v['term'] }}</td><td><div>{{ implode(' · ', $v['sets']) }}</div></td>
            <td><span class="badge badge-{{ $statusColors[$v['status']] }}">{{ $statusNames[$v['status']] }}</span>
                @if($v['book']?->published_version_id && $v['latest'] && (int)$v['latest']->id !== (int)$v['book']->published_version_id)<div class="eb-help mt-1">{{ __('ebooks.library.new_edition', ['status' => $statusNames[$v['latest']->status]]) }}</div>@endif
            </td><td>@if($v['latest']){{ $v['latest']->number }} <span class="eb-help">/ {{ trans_choice('ebooks.counts.pages', $v['latest']->page_count) }}</span>@else<span class="eb-help">—</span>@endif</td>
            <td><div class="eb-form-actions"><a class="btn btn-sm {{ $v['book'] ? 'btn-outline-primary' : 'btn-primary' }}" href="{{ route($routePrefix.'.index', ['section' => 'import', 'catalog_key' => $v['key']]) }}">{{ __($v['book'] ? 'ebooks.actions.import_edition' : 'ebooks.actions.import_pdf') }}</a>
            @if($v['book'])<a class="btn btn-sm btn-light" href="{{ route($routePrefix.'.index', ['section' => 'history', 'book' => $v['book']->id]) }}"><i class="fas fa-history mr-1"></i>{{ __('ebooks.actions.history') }}</a>@endif</div></td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted p-5">{{ __('ebooks.library.empty') }}</td></tr>@endforelse
    </tbody></table></div>
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap"><span class="eb-help">{{ __('ebooks.library.total_records', ['count' => number_format($library->total())]) }}</span>{{ $library->links('ebooks.pagination') }}</div>
</div>
